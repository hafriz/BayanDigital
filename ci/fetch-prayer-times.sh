#!/usr/bin/env bash
set -euo pipefail

repository_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
output_dir="${repository_root}/backend/resources/prayer-times"
api_endpoint="https://api.waktusolat.app/v2/solat"
year="$(date -u +%Y)"
month="$(date -u +%-m)"

mkdir -p "${output_dir}"

mapfile -t zones < <(
    sed -n "s/^[[:space:]]*'\([A-Z][A-Z][A-Z][0-9][0-9]\)' =>.*/\1/p" \
        "${repository_root}/backend/config/jakim.php" | sort -u
)

if [[ "${#zones[@]}" -lt 50 ]]; then
    echo "Expected at least 50 JAKIM zones, found ${#zones[@]}" >&2
    exit 1
fi

export api_endpoint output_dir year month
printf '%s\n' "${zones[@]}" | xargs -P 8 -n 1 bash -c '
    set -euo pipefail
    zone="$1"
    target="${output_dir}/${zone}-$(printf "%04d-%02d" "${year}" "${month}").json"
    curl --fail --silent --show-error --retry 3 --retry-all-errors \
        --get --data-urlencode "year=${year}" --data-urlencode "month=${month}" \
        --output "${target}" "${api_endpoint}/${zone}"
    jq -e --arg zone "${zone}" \
        ".zone == \$zone and (.prayers | length) >= 28" "${target}" >/dev/null
' _

echo "Fetched ${#zones[@]} prayer-time snapshots for $(printf '%04d-%02d' "${year}" "${month}")."
