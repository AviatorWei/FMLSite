#!/bin/bash
PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:~/bin
export PATH
nowtime=$(date +%s%N)
thentime=$((${nowtime}+300000000000))
while (( $(date +%s%N) > $nowtime ))
do
    if (( $(date +%s%N) > $thentime ))
    then
    wget https://fmlpku.com/History/tmpFMLlive.txt
    python ./BDWM_cli.py edit --id=PES --password-file=./PESpassword --board=Sports_Game --postid=23621240 --title=[FML]第11轮双线直播帖 --content-file=./tmpFMLlive.txt
    rm -rf tmpFMLlive.txt
    nowtime=$((${nowtime}+300000000000))
    thentime=$((${thentime}+300000000000))
    fi
done

