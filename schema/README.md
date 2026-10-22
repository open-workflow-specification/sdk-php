# DSL schema

`workflow.json` is the unmodified JSON schema of the Open Workflow Specification DSL,
copied from the [specification repository](https://github.com/open-workflow-specification/specification)
at tag `v1.0.1` (`schema/workflow.json`). It is bundled so that `WorkflowValidator`
works offline and deterministically.

To upgrade the SDK to a newer DSL release, replace this file with the schema of that
release, update `Workflow::DSL_VERSION`, refresh `tests/Fixtures/examples` from the
release's `examples` directory and adjust the model where the schema changed.
