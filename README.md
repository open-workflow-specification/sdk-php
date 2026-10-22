# Open Workflow Specification - PHP SDK
[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Fopen-workflow-specification%2Fsdk-php.svg?type=shield)](https://app.fossa.com/projects/git%2Bgithub.com%2Fopen-workflow-specification%2Fsdk-php?ref=badge_shield)

Provides the PHP API/SPI for the [Open Workflow Specification](https://github.com/open-workflow-specification/specification)
(formerly the Serverless Workflow Specification).

With the SDK you can:
* Programmatically build workflow definitions
* Parse workflow JSON and YAML definitions
* Validate workflow definitions against the DSL schema

### Status

The current SDK version conforms to the [Open Workflow Specification DSL v1.0.1](https://github.com/open-workflow-specification/specification/tree/v1.0.1).
Every example of the specification is parsed, serialized without loss and validated by the test suite.

Definitions written for the legacy 0.8 DSL are not supported by this version. Use the
[migration tool](https://github.com/open-workflow-specification/migration-tool) to convert them, or the
`1.0.0` release of this package for the legacy object model.

## Installation

```bash
composer require serverlessworkflow/sdk
```

PHP 8.2 or later is required.

## Build

Every DSL object is a class with public properties named after the DSL properties. Objects can be built from
arrays, from instances, or from a mix of both; task lists keep the order of their named tasks.

```php
use Serverless\Workflow\Document;
use Serverless\Workflow\Endpoint;
use Serverless\Workflow\Task\Call\HttpArguments;
use Serverless\Workflow\Task\CallHttp;
use Serverless\Workflow\Task\SetTask;
use Serverless\Workflow\TaskList;
use Serverless\Workflow\Timeout;
use Serverless\Workflow\Workflow;

$workflow = new Workflow([
    'document' => new Document([
        'dsl' => '1.0.1',
        'namespace' => 'examples',
        'name' => 'buy-pet',
        'version' => '0.1.0',
    ]),
    'do' => new TaskList([
        'getPet' => new CallHttp([
            'with' => new HttpArguments([
                'method' => 'get',
                'endpoint' => new Endpoint([
                    'uri' => 'https://petstore.swagger.io/v2/pet/{petId}',
                    'authentication' => [
                        'bearer' => [
                            'token' => '${ .token }',
                        ],
                    ],
                ]),
            ]),
        ]),
        'markAsSold' => new SetTask([
            'set' => [
                'status' => 'sold',
            ],
        ]),
        'buyPet' => new CallHttp([
            'with' => [
                'method' => 'put',
                'endpoint' => 'https://petstore.swagger.io/v2/pet/{petId}',
                'body' => '${ . + { status: .status } }',
            ],
            'then' => 'end',
        ]),
    ]),
    'timeout' => new Timeout([
        'after' => 'PT5M',
    ]),
]);
```

Tasks are resolved to their concrete type (`CallHttp`, `CallOpenApi`, `CallGrpc`, `CallAsyncApi`, `CallFunction`,
`DoTask`, `EmitTask`, `ForTask`, `ForkTask`, `ListenTask`, `RaiseTask`, `RunTask`, `SetTask`, `SwitchTask`,
`TryTask`, `WaitTask`) from their properties when they are created from arrays:

```php
use Serverless\Workflow\Task\Task;

$task = Task::fromArray(['wait' => 'PT30S']); // Serverless\Workflow\Task\WaitTask
```

Properties the model does not know are preserved as additional properties, so definitions using extensions or a
newer DSL can still be parsed, serialized and validated.

## Parse

### Convert from JSON/YAML source

```php
$workflow = Workflow::fromJson(file_get_contents('workflow.json'));

$workflow = Workflow::fromYaml(file_get_contents('workflow.yaml'));
```

### Navigate

```php
$getPet = $workflow->do->get('getPet');          // Serverless\Workflow\Task\CallHttp
$method = $getPet->with->method;                  // 'get'
$uri = $getPet->with->endpoint->uri;              // 'https://petstore.swagger.io/v2/pet/{petId}'

foreach ($workflow->do as $name => $task) {
    // ...
}
```

### Convert to JSON/YAML

```php
$json = $workflow->toJson();

$yaml = $workflow->toYaml();

$array = $workflow->toArray();
```

## Validate

```php
use Serverless\Workflow\WorkflowValidationException;
use Serverless\Workflow\WorkflowValidator;

try {
    $workflow->validate(); // or WorkflowValidator::validate($workflow);
} catch (WorkflowValidationException $exception) {
    foreach ($exception->getErrors() as $pointer => $messages) {
        // '/document' => ['The required properties (version) are missing']
    }
}

$errors = WorkflowValidator::errors($workflow); // [] when valid
```

Validation uses the DSL JSON schema bundled in [`schema/workflow.json`](schema/workflow.json), so it works
offline. Raw arrays can be validated too: `WorkflowValidator::errors(Yaml::parse($yaml))`.

## Development

```bash
composer install
composer run-script ecs   # fix coding style
composer run-script stan  # static analysis
composer run-script test  # test suite
```

## License
[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Fopen-workflow-specification%2Fsdk-php.svg?type=large)](https://app.fossa.com/projects/git%2Bgithub.com%2Fopen-workflow-specification%2Fsdk-php?ref=badge_large)
