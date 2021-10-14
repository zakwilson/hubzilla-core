This directory contains local copies of the w3.org jsonld documents

To update the activitystreams document run:
curl -L -H 'Accept: application/ld+json' https://www.w3.org/ns/activitystreams > activitystreams.jsonld

To update the identity document run:
curl -L -H 'Accept: application/ld+json' https://w3id.org/identity/v1 > identity-v1.jsonld

To update the security document run:
curl -L -H 'Accept: application/ld+json' https://w3id.org/security/v1 > security-v1.jsonld
