<p align="center">
  <img src="https://raw.githubusercontent.com/athenarc/schema/master/web/img/schema-logo2.png" width="400px"/>
  <h1 align="center">Scientific Containers on Heterogeneous Machines (SCHeMa)</h1>
  <br />
</p>

SCHeMa (Scheduler for scientific Containers on clusters of Heterogeneous Machines) is an open source platform to facilitate the execution and reproducibility of computational experiments on heterogeneous clusters. The platform exploits containerization, experiment packaging, and workflow management technologies to ease reproducibility, while it leverages machine learning technologies to automatically identify the type of node that is more suitable to undertake each submitted computational task.

**If you are using SCHeMa for your research please cite**: Thanasis Vergoulis, Konstantinos Zagganas, Loukas Kavouras, Martin Reczko, Stelios Sartzetakis, and Theodore Dalamagas. "SCHeMa: Scheduling Scientific Containers on a Cluster of Heterogeneous Machines." arXiv preprint arXiv:2103.13138 (2021).

## Deploying with Helm (recommended)
This helm chart includes the main SCHeMa web interface, along with a private docker registry, a postgres database server and an FTP server (for use by TESK). 

### Prerequisites
In order to be able to install SCHeMa you need:
* an operational Kubernetes cluster or minikube cluster ([tutorial](https://www.howtoforge.com/how-to-install-kubernetes-with-minikube-on-ubuntu-1804-lts/)) with metrics-server installed
* a ReadWriteMany Kubernetes StorageClass (like NFS)
* Helm v.3 and greater
* a [cwl-WES](https://github.com/elixir-cloud-aai/cwl-WES) (see below) in k8s namespace ```wes``` and [TESK](https://github.com/EMBL-EBI-TSI/TESK) in k8s namespace ```tes```, for workflow and task execution respectively.
* an [NGINX ingress controller](https://kubernetes.github.io/ingress-nginx/deploy/) with [cert-manager](https://cert-manager.io/docs/installation/kubernetes/) installed (see here: https://dev.to/chrisme/setting-up-nginx-ingress-w-automatically-generated-letsencrypt-certificates-on-kubernetes-4f1k)

### Deployment
1. Create a new namespace (schema) with 
```bash 
kubectl create namespace schema
```
2. Edit ```deployment/values.yaml``` and fill the values appropriate for your installation in the following fields:

| Name   | Description |
| ------ | ----------- |
| **domain** | The ingress domain name to deploy the apps |
| **schema.volume.deploy\_volume** | Whether to deploy a storage volume for the user data in SCHeMa |
| **schema.volume.size** | size of the volume (e.g. 50Gi) |
| **schema.volume.storageClass** | The name of the ReadWriteMany storageClass |
| **postgres.volume.deploy\_volume** | Whether to deploy a storage volume for the DB data |
| **postgres.volume.size** | same as schema.volume.size for the DB volume |
| **postgres.volume.storageClass** | Same as schema.volume.storageClass
| **postgres.deployment.dbUsername** | Username of the DB user |
| **postgres.deployment.dbPassword** | Password of the DB user |
| **postgres.deployment.dbName** | Name of the DB |
| **cluster\_endpoint** | Endpoint of the Kubernetes api server (e.g. https://xxx.xxx.xxx.xxx:443)
| **registry** | URL of the private registry |
| **registry.data\_volume.deploy\_volume** | Whether to deploy a storage volume for the registry data |
| **registry.data\_volume.size** | same as schema.volume.size for the registry data volume |
| **registry.data\_volume.storageClass** | Same as schema.volume.storageClass for the registry data volume |
| **registry.credentials\_volume.deploy\_volume** | Whether to deploy a storage volume for the registry authentication credentials |
| **registry.credentials\_volume.storageClass** | Same as schema.volume.storageClass |
| **registry.credentials\_volume.size** | We do not recommend anything greater than 10M  for this volume |
| **registry.deployment.username** | Your registry username |
| **registry.deployment.password** | Your registry password |
| **ftp.deployment.username** | Your FTP username |
| **ftp.deployment.password** | Your FTP password |
| **tesk.url** | The URL of your TESK installation |
| **wes.url** | The URL of your cwl-WES installation |
| **standalone.isStandalone** | Leave to "true" (unless you are running the [CLIMA](https://github.com/athenarc/clima) project management system.) |
| **standalone.Resources** | Maximum resources for job pods when running in standalone mode |
| **metrics.url** | Link to a metrics server dashboard of your choice (leave blank if not available) |

Note: you can either create Persistent Volume Claims (PVC) with the appropriate names in ```values.yaml``` or you can allow the helm chart to create them automatically.

3. Deploy the Helm chart with 
```bash
helm install schema-app deployment -f deployment/values.yaml
```

4. Create the database structure and add required data:
```bash
kubectl -n schema exec -it <schema-pod-id> -- psql -h postgres -U <your-db-username> -d <your-db-name> -f /app/web/schema/database_schema/schema_db.sql
```
5. Run the same command for all migration files ```/app/web/schema/database-schema/migration-xx.sql``` in order. If you are upgrading to the latest version of SCHeMa, please run the migration files that have been published since the last version.

After all steps have been completed the app should be running as expected. By default a superadministrator account is created and you can login using "superadmin" as username and password. Please change it as soon as possible after logging in. 


## Installing on a dedicated machine (Deprecated)
### Prerequisites
In order to install SCHeMa you need:
* an operational Kubernetes cluster or minikube cluster ([tutorial](https://www.howtoforge.com/how-to-install-kubernetes-with-minikube-on-ubuntu-1804-lts/)) with metrics-server installed
* a docker registry configured with TLS and basic authentication (or see below for installation instructions for a private local registry)
* an Apache server with PHP 7.2 installed on the cluster master or another machine that has access to the "kubectl" command
* a PostgreSQL database server
* python 3 and docker installed
* a local directory exposed via NFS (called local NFS from here on) to the cluster so that Kubernetes pods can read/write data from/on it ([tutorial](https://help.ubuntu.com/community/SettingUpNFSHowTo))
* a system user with sudo permissions that is able to run docker and kubectl without using sudo.
* a [cwl-WES](https://github.com/elixir-cloud-aai/cwl-WES) (see below) in k8s namespace ```wes``` and [TESK](https://github.com/EMBL-EBI-TSI/TESK) in k8s namespace ```tes```, for workflow and task execution respectively.
* a ReadWriteMany Kubernetes StorageClass (like NFS) for cwl-WES and TESK.

### Required PHP packages
The node running the installation of SCHeMa should have the following PHP packages installed:
* php-mbstring
* php-xml
* php-gd
* php-pgsql
* php-yaml

### Required Python packages
The node running the installation of SCHeMa should have the following Python packages installed:
* python3-ruamel.yaml
* python3-psycopg2
* python3-yaml
* python3-requests
* rocrate (install with pip3)
* python3-sklearn
* dockertarpusher (install with pip3)

### Other packages required:
* cwltool
* graphviz


## Installing a local docker registry with self-signed certificates and basic authentication
On the machine that will run the SCHeMa installation:
1. Create a folder for the registry certificates and authentication files (e.g. /data/registry) with two additional directories, "certs" and reg_auth".
2. Create self-signed certificates:
```bash
openssl req \
  -newkey rsa:4096 -nodes -sha256 -keyout <registry_data_directory>/certs/domain.key \
  -x509 -days 365 -out <registry_data_directory>/certs/domain.crt
````
3. Create a username and password for the registry (change ```<registry_username>``` and ```<registry_username>``` appropriately):
```bash
sudo docker run -it --entrypoint htpasswd -v $PWD/reg_auth:/auth -w /auth registry:2 -Bbc /auth/htpasswd <registry_username> <registry_password>
```
4. Start the registry with the created certificates:
```bash
  docker run -d \
  --restart=always \
  --name registry \
  -v "$(pwd)"/certs:/certs \
  -v "$(pwd)"/reg_auth:/auth \
  -e REGISTRY_HTTP_ADDR=0.0.0.0:5000 \
  -e REGISTRY_HTTP_TLS_CERTIFICATE=/certs/domain.crt \
  -e REGISTRY_HTTP_TLS_KEY=/certs/domain.key \
  -e "REGISTRY_AUTH=htpasswd" \
  -e "REGISTRY_AUTH_HTPASSWD_REALM=Registry Realm" \
  -e REGISTRY_AUTH_HTPASSWD_PATH=/auth/htpasswd \
  -e REGISTRY_STORAGE_DELETE_ENABLED=true \
  -p 5000:5000 \
  registry:2
```
5. Create folders with the certificate for the docker registry and copy the certificates:
```bash
sudo mkdir -p /etc/docker/certs.d/127.0.0.1:5000
sudo mkdir -p /etc/docker/certs.d/localhost:5000
sudo cp <registry_data_directory>/certs/domain.crt /etc/docker/certs.d/127.0.0.1:5000/ca.crt
sudo cp <registry_data_directory>/certs/domain.crt /etc/docker/certs.d/localhost:5000/ca.crt
```
6. Login to the registry:
```bash
docker login 127.0.0.1:5000 -u <registry_username> -p pass <registry_password>
```
7. Create a Kubernetes secret named `docker-secret` with your Docker login. This is so that Kubernetes can retrieve images from your private registry:
```bash
kubectl create secret docker-registry --docker-server <docker-registry-ip> --docker-username <registry_username> --docker-password <registry_password>
```

## Installing SCHeMa

1. Install the Yii2 framework([tutorial](https://www.yiiframework.com/doc/guide/2.0/en/start-installation)) and install the following plugins:
  * [Webvimark User management](https://github.com/webvimark/user-management) without migrating the database.
  * [DatePicker](https://demos.krajee.com/widget-details/datepicker)
  * [Yii2 Bootstrap4](https://github.com/yiisoft/yii2-bootstrap4)
  * [Yii http requests](https://github.com/yiisoft/yii2-httpclient)
  * [elFinder](https://github.com/alexantr/yii2-elfinder)
  * [ckeditor](https://github.com/2amigos/yii2-ckeditor-widget)

2. Download the SCHeMa code from GitHub and replace the files inside the Yii project folder.

3. Create a postgres database named "schema" for user "schema".

4. Restore the .sql file inside the "database_schema" folder as user "postgres" to the database created in the previous step:
  ```sudo -u postgres psql -d schema -f <path_to_database_schema>/database_schema.sql```

5. Copy the docker registry certificates in the project_root/scheduler_files/certificates:
```cp <registry_data_directory>/certs/* <path_to_schema_project>/scheduler_files/certificates```

6. Using root permissions create an empty file inside /etc/sudoers.d/ with ```visudo``` and paste the following inside it after filling the relevant information:
```bash
www-data ALL=(<user>) NOPASSWD: <path-to-kubectl>, <path-to-docker>, <path_to_schema_project>/scheduler_files/scheduler.py, <path_to_schema_project>/scheduler_files/ontology/initialClassify.py, <path_to_schema_project>/scheduler_files/imageUploader.py, <path_to_schema_project>/scheduler_files/imageRemover.py, <path_to_schema_project>/scheduler_files/inputReplacer.py, <path_to_schema_project>/scheduler_files/probe_stats.py, <path_to_schema_project>/scheduler_files/setupMpiCluster.py, <path_to_schema_project>/scheduler_files/mpiMonitorAndClean.py, <path_to_schema_project>/scheduler_files/existingImageUploader.py, <path_to_schema_project>/scheduler_files/workflowMonitorAndClean.py, <path_to_schema_project>/scheduler_files/workflowUploader.py, <path_to_cwltool>/cwltool
```
  where ```<user>```: a user that has permissions to run path-to-kubectl. As an example take a look at the following

```bash
  www-data ALL=(ubuntu) NOPASSWD: /usr/bin/kubectl, /data/www/schema/scheduler_files/scheduler.py, /data/www/schema/scheduler_files/ontology/initialClassify.py, /data/www/schema/scheduler_files/imageUploader.py, /data/www/schema/scheduler_files/imageRemover.py, /data/www/schema/scheduler_files/inputReplacer.py, /data/www/schema/scheduler_files/probe_stats.py, /data/www/schema/scheduler_files/setupMpiCluster.py,/data/www/schema/scheduler_files/mpiMonitorAndClean.py, /data/www/schema/scheduler_files/existingImageUploader.py, /data/www/schema/scheduler_files/workflowMonitorAndClean.py, /data/www/schema/scheduler_files/workflowUploader.py
```

  This will allow www-data to run kubectl and the python scripts inside the folder as the user you have selected.

7. Inside the project folder change the following files according to the database and Docker registry configuration:
  * scheduler_files/configuration.json using the template found at scheduler_files/configuration-template.json and fill the appropriate details.
  * config/db.php and fill the details for the database (for details see the Yii2 documentation)
  * config/params.php and fill the following details according to your configuration (you can use params-template.php):

8. Create a new namespace in Kubernetes for the Open MPI Cluster:
```bash
kubectl create namespace mpi-cluster
```
