CODE=$(curl --write-out '%{http_code}' --output /dev/null --silent localhost:8080/index.php?r=site/health)
echo $CODE
if [ $CODE != "200" ]
then
  exit 1;
fi