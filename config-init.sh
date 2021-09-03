mkdir /data/docker/RO-crates -p
mkdir /data/docker/user-data -p
mkdir /data/docker/tmp-images -p
mkdir /data/docker/tmp -p
mkdir /data/docker/workflows/tmp-workflows -p
mkdir /data/docker/workflows-svg -p
mkdir /data/docker/archived_workflows -p
mkdir /data/docker/profiles -p
touch /data/containerized
mkdir /root/.kube/
chmod 777 /data/docker -R



## Create a kubeconfig file in order for www-data to be able to run kubectl commands
KUBECFG_FILE_NAME="/root/.kube/config"
SECRETS_FOLDER="/run/secrets/kubernetes.io/serviceaccount"
SERVICE_ACCOUNT_NAME="schema-kubectl"
NAMESPACE=$(cat ${SECRETS_FOLDER}/namespace)
CLUSTER_NAME="schema-cluster"
ENDPOINT=$CLUSTER_ENDPOINT
CERTAUTH=${SECRETS_FOLDER}/ca.crt
USER_TOKEN=$(cat ${SECRETS_FOLDER}/token)

# Set up the config
echo -e "\\nPreparing k8s-${SERVICE_ACCOUNT_NAME}-${NAMESPACE}-conf"
echo -n "Setting a cluster entry in kubeconfig..."
kubectl config set-cluster "${CLUSTER_NAME}" \
--kubeconfig="${KUBECFG_FILE_NAME}" \
--server="${ENDPOINT}" \
--certificate-authority="${SECRETS_FOLDER}/ca.crt" \
--embed-certs=true

echo -n "Setting token credentials entry in kubeconfig..."
kubectl config set-credentials \
"${SERVICE_ACCOUNT_NAME}-${NAMESPACE}-${CLUSTER_NAME}" \
--kubeconfig="${KUBECFG_FILE_NAME}" \
--token="${USER_TOKEN}"

echo -n "Setting a context entry in kubeconfig..."
kubectl config set-context \
"${SERVICE_ACCOUNT_NAME}-${NAMESPACE}-${CLUSTER_NAME}" \
--kubeconfig="${KUBECFG_FILE_NAME}" \
--cluster="${CLUSTER_NAME}" \
--user="${SERVICE_ACCOUNT_NAME}-${NAMESPACE}-${CLUSTER_NAME}" \
--namespace="${NAMESPACE}"

echo -n "Setting the current-context in the kubeconfig file..."
kubectl config use-context "${SERVICE_ACCOUNT_NAME}-${NAMESPACE}-${CLUSTER_NAME}" \
--kubeconfig="${KUBECFG_FILE_NAME}"


# Run the main apache process
apache2-foreground
