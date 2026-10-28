#!/usr/bin/env bash
set -euo pipefail

: "${ORGS_ADMIN_USER:?ORGS_ADMIN_USER fehlt}"
: "${ORGS_ADMIN_PASSWORD:?ORGS_ADMIN_PASSWORD fehlt}"

base_url="${ORGS_BASE_URL:-https://nextcloud-dev.ddev.site}"
script_dir="$(cd "$(dirname "$0")" && pwd)"
ddev_project="${ORGS_DDEV_PROJECT:-$(cd "$script_dir/../../nextcloud-dev" && pwd)}"
suffix="$(date +%s)-$$"
nonadmin="orgs-smoke-${suffix}"
workdir="$(mktemp -d)"
external_links_changed=0
external_links_had_key=0
admin_token=''
admin_cookies="$workdir/admin-cookies.txt"
external_original="$workdir/external-original.json"
external_restore="$workdir/external-restore.json"

occ() {
    (cd "$ddev_project" && ddev exec -d /var/www/html/html php occ "$@")
}

if occ config:list orgsuite | php -r '$data=json_decode(stream_get_contents(STDIN),true,flags:JSON_THROW_ON_ERROR); exit(array_key_exists("external_links",$data["apps"]["orgsuite"]??[])?0:1);'; then
    external_links_had_key=1
fi
restore_external_links() {
    curl --silent --show-error --insecure --user "$ORGS_ADMIN_USER:$ORGS_ADMIN_PASSWORD" \
        --cookie "$admin_cookies" --cookie-jar "$admin_cookies" -H "requesttoken: $admin_token" -H 'Content-Type: application/json' \
        -X POST --data "@$external_restore" "$base_url/index.php/apps/orgsuite/api/admin/external-links" >/dev/null
    if [[ "$external_links_had_key" == '0' ]]; then
        occ config:app:delete orgsuite external_links >/dev/null
    fi
}

cleanup() {
    if [[ "$external_links_changed" == '1' && -n "$admin_token" && -s "$external_restore" ]]; then
        restore_external_links || true
    fi
    occ user:delete "$nonadmin" >/dev/null 2>&1 || true
    rm -rf "$workdir"
}
trap cleanup EXIT

admin_headers="$workdir/admin-headers.txt"
status="$(curl --silent --show-error --insecure --user "$ORGS_ADMIN_USER:$ORGS_ADMIN_PASSWORD" \
    --dump-header "$admin_headers" --output /dev/null --write-out '%{http_code}' \
    "$base_url/index.php/apps/orgsuite/flz")"
if [[ "$status" != '303' ]] || ! grep -qiE '^location: .*apps/flzcalendar/?' "$admin_headers"; then
    echo 'Der FLZ-Einstieg leitet nicht bevorzugt zum Kalender weiter.' >&2
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
admin_settings="$workdir/admin-settings.json"
curl --fail --silent --show-error --insecure --user "$ORGS_ADMIN_USER:$ORGS_ADMIN_PASSWORD" \
    --cookie-jar "$admin_cookies" "$base_url/index.php/apps/flzcalendar/" --output "$admin_page"
admin_token="$(sed -n 's/.*data-requesttoken="\([^"]*\)".*/\1/p' "$admin_page" | head -n 1)"
if [[ -z "$admin_token" ]]; then
    echo 'Admin-Request-Token fehlt.' >&2
    exit 1
fi
curl --fail --silent --show-error --insecure --user "$ORGS_ADMIN_USER:$ORGS_ADMIN_PASSWORD" \
    --cookie "$admin_cookies" --cookie-jar "$admin_cookies" -H "requesttoken: $admin_token" \
    "$base_url/index.php/apps/localbase/api/flz-full-suite/admin/settings" --output "$admin_settings"
for contract in '"organization"' '"calendarPeerEditing"' '"vacationPeerApproval"'; do
    if ! grep -q "$contract" "$admin_settings"; then
        echo "Admin-API-Vertrag fehlt: $contract" >&2
        exit 1
    fi
done

external_endpoint="$base_url/index.php/apps/orgsuite/api/admin/external-links"
curl --fail --silent --show-error --insecure --user "$ORGS_ADMIN_USER:$ORGS_ADMIN_PASSWORD" \
    --cookie "$admin_cookies" --cookie-jar "$admin_cookies" -H "requesttoken: $admin_token" \
    "$external_endpoint" --output "$external_original"
php -r '
$data = json_decode(file_get_contents($argv[1]), true, flags: JSON_THROW_ON_ERROR);
file_put_contents($argv[2], json_encode(["links" => $data["links"], "order" => $data["order"]], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
' "$external_original" "$external_restore"
status="$(curl --silent --show-error --insecure --user "$ORGS_ADMIN_USER:$ORGS_ADMIN_PASSWORD" \
    --cookie "$admin_cookies" --cookie-jar "$admin_cookies" -H 'Content-Type: application/json' \
    -X POST --data '{"links":[]}' --output "$workdir/external-csrf.json" --write-out '%{http_code}' \
    "$external_endpoint")"
if [[ "$status" != '412' ]]; then
    echo "Externe-Link-Schreibzugriff ohne CSRF-Token ergab HTTP $status statt 412." >&2
    exit 1
fi

external_payload="$workdir/external-payload.json"
external_saved="$workdir/external-saved.json"
php -r '
require $argv[1];
$data = json_decode(file_get_contents($argv[2]), true, flags: JSON_THROW_ON_ERROR);
$links = [
    ["id" => "smoke-flz-docs", "suite" => "flz", "label" => "Smoke FLZ Docs", "url" => "https://docs.example.test/flz", "active" => true, "openMode" => "new"],
    ["id" => "smoke-br-portal", "suite" => "br", "label" => "Smoke BR Portal", "url" => "https://portal.example.test/br", "active" => true, "openMode" => "same"],
];
$payload = \OCA\OrgSuite\Tests\Support\ExternalLinkSmokePayloadBuilder::replaceLinks($data, $links);
file_put_contents($argv[3], json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
' "$script_dir/Support/ExternalLinkSmokePayloadBuilder.php" "$external_original" "$external_payload"
curl --fail --silent --show-error --insecure --user "$ORGS_ADMIN_USER:$ORGS_ADMIN_PASSWORD" \
    --cookie "$admin_cookies" --cookie-jar "$admin_cookies" -H "requesttoken: $admin_token" -H 'Content-Type: application/json' \
    -X POST --data "@$external_payload" "$external_endpoint" --output "$external_saved"
external_links_changed=1
for contract in '"id":"smoke-flz-docs"' '"id":"smoke-br-portal"' '"openMode":"new"' '"openMode":"same"' '"external:smoke-flz-docs"'; do
    if ! grep -q "$contract" "$external_saved"; then
        echo "Gespeicherter externer Link fehlt: $contract" >&2
        exit 1
    fi
done

curl --fail --silent --show-error --insecure --user "$ORGS_ADMIN_USER:$ORGS_ADMIN_PASSWORD" \
    --cookie "$admin_cookies" --cookie-jar "$admin_cookies" \
    "$base_url/index.php/apps/flzcalendar/" --output "$workdir/menu-page.html"
php -r '
$html = file_get_contents($argv[1]);
if (!preg_match("/<input[^>]+id=\"initial-state-orgsuite-suite-navigation\"[^>]*>/", $html, $tag)
    || !preg_match("/value=\"([^\"]+)\"/", $tag[0], $value)) {
    throw new RuntimeException("Suite-Menü-Initialzustand fehlt.");
}
$state = json_decode(base64_decode(html_entity_decode($value[1])), true, flags: JSON_THROW_ON_ERROR);
$flz = $state["flz"]["items"] ?? [];
$br = $state["br"]["items"] ?? [];
if (($flz[0]["href"] ?? null) !== "https://docs.example.test/flz" || ($flz[0]["openMode"] ?? null) !== "new"
    || ($br[0]["href"] ?? null) !== "https://portal.example.test/br" || ($br[0]["openMode"] ?? null) !== "same") {
    throw new RuntimeException("Gespeicherte Öffnungsmodi oder gemeinsame Reihenfolge fehlen im ausgelieferten Suite-Menü.");
}
' "$workdir/menu-page.html"

status="$(curl --silent --show-error --insecure --user "$ORGS_ADMIN_USER:$ORGS_ADMIN_PASSWORD" \
    --cookie "$admin_cookies" --cookie-jar "$admin_cookies" -H 'Content-Type: application/json' \
    -X PUT --data '{}' --output "$workdir/csrf.json" --write-out '%{http_code}' \
    "$base_url/index.php/apps/localbase/api/flz-full-suite/admin/permissions")"
if [[ "$status" != '412' ]]; then
    echo "Admin-Schreibzugriff ohne CSRF-Token ergab HTTP $status statt 412." >&2
    exit 1
fi

(cd "$ddev_project" && ddev exec -d /var/www/html/html env OC_PASS="$nonadmin" php occ user:add --password-from-env "$nonadmin") >/dev/null
nonadmin_page="$workdir/nonadmin-page.html"
nonadmin_cookies="$workdir/nonadmin-cookies.txt"
curl --fail --silent --show-error --insecure --user "$nonadmin:$nonadmin" \
    --cookie-jar "$nonadmin_cookies" "$base_url/index.php/apps/flzcalendar/" --output "$nonadmin_page"
nonadmin_token="$(sed -n 's/.*data-requesttoken="\([^"]*\)".*/\1/p' "$nonadmin_page" | head -n 1)"
if [[ -z "$nonadmin_token" ]]; then
    echo 'Request-Token des Standardkontos fehlt.' >&2
    exit 1
fi
status="$(curl --silent --show-error --insecure --user "$nonadmin:$nonadmin" \
    --cookie "$nonadmin_cookies" --cookie-jar "$nonadmin_cookies" -H "requesttoken: $nonadmin_token" \
    --output "$workdir/denied.json" --write-out '%{http_code}' \
    "$base_url/index.php/apps/localbase/api/flz-full-suite/admin/settings")"
if [[ "$status" != '403' ]]; then
    echo "Standardkonto erhielt beim Admin-Endpunkt HTTP $status statt 403." >&2
    exit 1
fi

status="$(curl --silent --show-error --insecure --user "$nonadmin:$nonadmin" \
    --cookie "$nonadmin_cookies" --cookie-jar "$nonadmin_cookies" -H "requesttoken: $nonadmin_token" \
    --output "$workdir/external-denied.json" --write-out '%{http_code}' "$external_endpoint")"
if [[ "$status" != '403' ]]; then
    echo "Standardkonto erhielt bei der externen Linkadministration HTTP $status statt 403." >&2
    exit 1
fi

restore_external_links
external_links_changed=0

echo 'OrgSuite HTTP smoke: OK'
