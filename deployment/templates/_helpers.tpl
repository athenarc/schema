{{/*
Create secret to access docker registry
*/}}
{{- define "deployment.imagePullSecret" }}
{{- printf "{\"auths\": {\"%s\": {\"username\":\"%s\",\"password\":\"%s\",\"auth\": \"%s\"}}}" .Values.registry.url .Values.registry.deployment.username .Values.registry.deployment.password (printf "%s:%s" .Values.registry.deployment.username .Values.registry.deployment.password | b64enc) | b64enc }}
{{- end }}

{{- define "deployment.postgresql_user" }}
{{- if hasPrefix "centos" .Values.postgres.deployment.image }}
{{- print "POSTGRESQL_USER" }}
{{- else }}
{{- print "POSTGRES_USER" }}
{{- end }}
{{- end }}

{{- define "deployment.postgresql_password" }}
{{- if hasPrefix "centos" .Values.postgres.deployment.image -}}
{{- print "POSTGRESQL_PASSWORD" -}}
{{- else -}}
{{- print "POSTGRES_PASSWORD" -}}
{{- end -}}
{{- end }}

{{- define "deployment.postgresql_database" }}
{{- if hasPrefix "centos" .Values.postgres.deployment.image -}}
{{- print "POSTGRESQL_DATABASE" -}}
{{- else -}}
{{- print "POSTGRES_DATABASE" -}}
{{- end -}}
{{- end }}