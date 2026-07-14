#!/usr/bin/env bash
set -euo pipefail

: "${ORGS_ADMIN_USER:?ORGS_ADMIN_USER fehlt}"
: "${ORGS_ADMIN_PASSWORD:?ORGS_ADMIN_PASSWORD fehlt}"

base_url="${ORGS_BASE_URL:-https://nextcloud-dev.ddev.site}"
ddev_project="${ORGS_DDEV_PROJECT:-$(cd "$(dirname "$0")/../../nextcloud-dev" && pwd)}"
suffix="$(date +%s)-$$"
nonadmin="orgs-smoke-${suffix}"
nonadmin_password="Orgs-Smoke-${suffix}!"
workdir="$(mktemp -d)"

occ() {
    (cd "$ddev_project" && ddev exec -d /var/www/html/html php occ "$@")
}

cleanup() {
    occ user:delete "$nonadmin" >/dev/null 2>&1 || true
    rm -rf "$workdir"
}
trap cleanup EXIT

admin_headers="$workdir/admin-headers.txt"
status="$(curl --silent --show-error --insecure --user "$ORGS_ADMIN_USER:$ORGS_ADMIN_PASSWORD" \
    --dump-header "$admin_headers" --output /dev/null --write-out '%{http_code}' \
    "$base_url/index.php/apps/orgsuite/ad")"
if [[ "$status" != '303' ]] || ! grep -qiE '^location: .*apps/adcalendar/?' "$admin_headers"; then
    echo 'Der AD-Einstieg leitet nicht bevorzugt zum Kalender weiter.' >&2
    exit 1
fi

status="$(curl --silent --show-error --insecure --user "$ORGS_ADMIN_USER:$ORGS_ADMIN_PASSWORD" \
    --dump-header "$admin_headers" --output /dev/null --write-out '%{http_code}' \
    "$base_url/index.php/apps/orgsuite/br")"
if [[ "$status" != '303' ]] || ! grep -qiE '^location: .*apps/brtop/?' "$admin_headers"; then
    echo 'Der BR-Einstieg leitet nicht bevorzugt zu BRTop weiter.' >&2
    exit 1
fi

admin_page="$workdir/admin-page.html"
admin_cookies="$workdir/admin-cookies.txt"
admin_settings="$workdir/admin-settings.json"
curl --fail --silent --show-error --insecure --user "$ORGS_ADMIN_USER:$ORGS_ADMIN_PASSWORD" \
    --cookie-jar "$admin_cookies" "$base_url/index.php/apps/adcalendar/" --output "$admin_page"
admin_token="$(sed -n 's/.*data-requesttoken="\([^"]*\)".*/\1/p' "$admin_page" | head -n 1)"
if [[ -z "$admin_token" ]]; then
    echo 'Admin-Request-Token fehlt.' >&2
    exit 1
fi
curl --fail --silent --show-error --insecure --user "$ORGS_ADMIN_USER:$ORGS_ADMIN_PASSWORD" \
    --cookie "$admin_cookies" --cookie-jar "$admin_cookies" -H "requesttoken: $admin_token" \
    "$base_url/index.php/apps/orgsuite/api/admin/settings" --output "$admin_settings"
for contract in '"organization"' '"calendarPeerEditing"' '"vacationPeerApproval"'; do
    if ! grep -q "$contract" "$admin_settings"; then
        echo "Admin-API-Vertrag fehlt: $contract" >&2
        exit 1
    fi
done

status="$(curl --silent --show-error --insecure --user "$ORGS_ADMIN_USER:$ORGS_ADMIN_PASSWORD" \
    --cookie "$admin_cookies" --cookie-jar "$admin_cookies" -H 'Content-Type: application/json' \
    -X PUT --data '{}' --output "$workdir/csrf.json" --write-out '%{http_code}' \
    "$base_url/index.php/apps/orgsuite/api/admin/permissions")"
if [[ "$status" != '412' ]]; then
    echo "Admin-Schreibzugriff ohne CSRF-Token ergab HTTP $status statt 412." >&2
    exit 1
fi

(cd "$ddev_project" && ddev exec -d /var/www/html/html env OC_PASS="$nonadmin_password" php occ user:add --password-from-env "$nonadmin") >/dev/null
nonadmin_page="$workdir/nonadmin-page.html"
nonadmin_cookies="$workdir/nonadmin-cookies.txt"
curl --fail --silent --show-error --insecure --user "$nonadmin:$nonadmin_password" \
    --cookie-jar "$nonadmin_cookies" "$base_url/index.php/apps/adcalendar/" --output "$nonadmin_page"
nonadmin_token="$(sed -n 's/.*data-requesttoken="\([^"]*\)".*/\1/p' "$nonadmin_page" | head -n 1)"
if [[ -z "$nonadmin_token" ]]; then
    echo 'Request-Token des Standardkontos fehlt.' >&2
    exit 1
fi
status="$(curl --silent --show-error --insecure --user "$nonadmin:$nonadmin_password" \
    --cookie "$nonadmin_cookies" --cookie-jar "$nonadmin_cookies" -H "requesttoken: $nonadmin_token" \
    --output "$workdir/denied.json" --write-out '%{http_code}' \
    "$base_url/index.php/apps/orgsuite/api/admin/settings")"
if [[ "$status" != '403' ]]; then
    echo "Standardkonto erhielt beim Admin-Endpunkt HTTP $status statt 403." >&2
    exit 1
fi

echo 'OrgSuite HTTP smoke: OK'
