#!/bin/bash
# Generate a private key and public key pair

# Pair name (default "noname")
NAME=${1:-noname}

# Key size (default 4096)
SIZE=${2:-4096}

# Key pair destination (default subfolder called keys/)
DEST=${3:-keys/}

# Make destination folder, if it doesn't exist
mkdir -p $DEST

# Public/Private key files
PRIK=$DEST$NAME.pem
PUBK=$DEST$NAME.pub

# Optional unencrpyted private key
PCLR=$DEST$NAME.key

# If either exists, avoid overwrite
if [ -f "$PRIK" ] || [ -f "$PUBK" ]; then
	echo "A key by that name already exists"
	exit 1
fi

# Generate encrypted private key and plaintext public key
# This will prompt for a password 
openssl genrsa -aes256 -out $PRIK $SIZE && 
openssl rsa -in $PRIK -out $PUBK -pubout

exit

# To generate unencrypted private key (not recommended):
# openssl genrsa -out $PRIK $SIZE

# To extract private key from encrypted pem:
# openssl pkcs8 -topk8 -inform PEM -outform PEM -nocrypt -in $PRIK -out $PCLR


# Call this file with : sh genkeys.sh

# For key pair named "keyname"
# sh genkeys.sh keyname

# For 3248 bit keys:
# sh genkeys.sh keyname 3248

# For a keypair saved to /custom_dir
# sh genkeys.sh keyname 4096 /custom_dir/

