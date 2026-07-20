#!/usr/bin/env bash
set -euo pipefail

repository_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
gradle_file="${repository_root}/android/app/build.gradle.kts"
metadata_file="${repository_root}/backend/public/android/latest.json"

version_name="$(sed -n 's/^[[:space:]]*versionName = "\([^"]*\)".*/\1/p' "${gradle_file}" | head -n 1)"
version_code="$(sed -n 's/^[[:space:]]*versionCode = \([0-9][0-9]*\).*/\1/p' "${gradle_file}" | head -n 1)"

if [[ -z "${version_name}" || -z "${version_code}" ]]; then
    echo 'Unable to read Android versionName or versionCode.' >&2
    exit 1
fi

temporary_file="$(mktemp)"
trap 'rm -f "${temporary_file}"' EXIT

jq --arg version_name "${version_name}" --argjson version_code "${version_code}" \
    --arg apk_url "/android/masjid-smart-screen-tv.apk?v=${version_code}" \
    '.version_name = $version_name | .version_code = $version_code | .apk_url = $apk_url' \
    "${metadata_file}" > "${temporary_file}"
mv "${temporary_file}" "${metadata_file}"
trap - EXIT

echo "Android website metadata updated to ${version_name} (${version_code})."
