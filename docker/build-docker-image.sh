#!/bin/bash
name=jnmik/crud-admin-generator

version=
while [ -z $version ]; do
  printf "\e[1;46mEnter the tag of your image:\e[0m ";
  read -r version;
done

branch=$(git branch | grep '*')
echo -e "you are building an image from the following branch of your project : \e[1;41m${branch:2}\e[0m"
echo -e "your image will be \e[1;41m$name:$version\e[0m"
echo -e "do you want to continue? [Y/n]"
read accept

if [ "$accept" != "Y" ]; then
  echo -e "\e[0;41mprocess aborted by user!\e[0m";
  exit 1;
fi

echo -e "\e[1;30;43m-- Starting to build docker image --\e[0m"
docker build -t $name:$version .

if [ $? -ne 0 ];
  then
    echo -e "\e[0;41mTask build docker image failed and return an non 0 code. Abort!\e[0m";
    exit 4;
  else
    echo -e "\e[0;42mTask build docker image successfully executed\e[0m"
fi

echo -e "Do you want to push the new image to the repository? [Y/n]"
read push

if [ "$push" != "Y" ]; then
  echo -e "\e[0;42mDocker image will not be push. Use can use it localy! Process over.\e[0m";
  exit 5;
fi

echo -e "\e[1;30;43m-- Starting to push image to docker hub --\e[0m"
docker push $name:$version

if [ $? -ne 0 ];
  then
    echo -e "\e[0;41mTask push docker image failed and return an non 0 code. Abort!\e[0m";
    exit 6;
  else
    echo -e "\e[0;42mTask psuh docker image successfully executed\e[0m"
fi

echo -e "Build LATEST and push it  ? [Y/n]"
read pushlatest

if [ "$pushlatest" != "Y" ]; then
    echo -e "\e[0;42mLatest image will not be built & pushed. Process over.\e[0m";
    exit 7;
fi

imageId=$(docker images | grep $name | grep $version | awk '{ print $3; }')
docker tag -f $imageId $name:latest
docker push $name:latest