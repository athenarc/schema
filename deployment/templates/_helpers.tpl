{{/*
Create secret to access docker registry
*/}}
{{- define "imagePullSecret" }}
{{- printf "{\"auths\": {\"%s\": {\"username\":\"%s\",\"password\":\"%s\",\"auth\": \"%s\"}}}" .Values.registry.url .Values.registry.deployment.username .Values.registry.deployment.password (printf "%s:%s" .Values.registry.deployment.username .Values.registry.deployment.password | b64enc) | b64enc }}
{{- end }}
