#!/bin/bash

content=$(cat /root/sshpass)

IFS=':' read -r -a parts <<< "$content"

# Değişkenleri tanımlayalım
HOSTNAME=${parts[0]}
PASSWORD=${parts[1]}

sshpass -p $PASSWORD ssh $HOSTNAME -t "cd Desktop/synapseui\(synaptic\ framework\) && docker compose exec -it ollama sh"