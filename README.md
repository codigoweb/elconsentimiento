# Elconsentimiento

Custom module to connect elconsentimiento.es API

## Installation
1. Add next lines to section `repositories` into `composer.json` file.

```
        {
            "type": "vcs",
            "url":  "git@bitbucket.org:navandu/elconsentimiento.git"
        }
```

2. Make sure the next lines are at section `installer-paths` into `composer.json` file.
```
"web/modules/contrib/{$name}": ["type:drupal-module","type:drupal-custom-module"],
```

2. run `composer require navandu/elconsentimiento`



## Postman
The file `Postman/SuperDocs.postman_collection.json` contains a Postman collection to test API

The file `Postman/Testing.postman_environment.json` contains a Testing environment

### Endpoints
```
POST {{api_url}}/login
GET {{api_url}}/rgpd/user
GET {{api_url}}/rgpd/devices
GET {{api_url}}/rgpd/templates
GET {{api_url}}/template/{uuid}
GET {{api_url}}/template/{uuid}/variables?
GET {{api_url}}/rgpd/documents?order=creationDate
GET {{api_url}}/rgpd/document/{uuid}
POST {{api_url}}/rgpd/document
```
### Calback url
When the status of document change, they send us a json file to our endpoint.
```
POST {{BASE_PATH}}/api/v1/elconsentimiento/callback
```

## Configuration
All endpoint and general parameters must be configure in `{{BASE_PATH}}/admin/config/services/elconsentimiento`

## Services
`ElconsentimientoService.php` contains all GET and POST methods we need to connect the endpoints

The next methods may be called from external modules:
```
      \Drupal::service('elconsentimiento.manager')->getUser();
      \Drupal::service('elconsentimiento.manager')->getDevices();
      \Drupal::service('elconsentimiento.manager')->getDocuments();
      \Drupal::service('elconsentimiento.manager')->getDocument($uuid);
      \Drupal::service('elconsentimiento.manager')->getDocumentStatus($uuid);
      \Drupal::service('elconsentimiento.manager')->getTemplates();
      \Drupal::service('elconsentimiento.manager')->getTemplate($uuid);
      \Drupal::service('elconsentimiento.manager')->getTemplateVariables($uuid);
      \Drupal::service('elconsentimiento.manager')->buildPost($uuid, $signer, $vars);
      \Drupal::service('elconsentimiento.manager')->postDocument($body);
      \Drupal::service('elconsentimiento.manager')->statusCallback($payload);
```

## Event
We create a new event called `elconsentimiento.status_changed` to alert system when the status document was changed 
throw callbak endpoint. 

This even is defined in the files `src/Event/ElconsentimientoEvents.php` and `src/Event/ElconsentimientoStatusEvent.php` 
and be called from a rest resource in `src/Plugin/rest/resource/ElconsentimientoRestResource.php` file.

This event can be subscribed to manage callback response creating a EventSubscriber.
`src/EventSubscriber/ElconsentimientoEventSubscriber.php` it's a example file
