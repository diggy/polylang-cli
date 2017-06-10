#!/usr/bin/env bash

#set -ex

PACKAGE_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )"/../ && pwd )"

generate_docs() {
    wp scaffold package-readme $PACKAGE_DIR --force
}

generate_docs
