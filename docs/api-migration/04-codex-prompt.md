# Follow-up implementation guidance

The original milestone-1-only prompt has been fulfilled and superseded by the expanded backend migration. Do not use it to narrow the implemented scope.

For follow-up work, read the current OpenAPI, feature-parity matrix, setup, deployment and verification documents. Preserve existing UI and legacy API consumers until the cutover checklist is accepted. Use disposable databases and fake delivery in tests; never reset an existing application database.

Next work is React integration and real delivery/provider acceptance, followed by production-engine rehearsal and MVC cutover. Backend changes must keep route/schema coverage tests and the OpenAPI contract synchronized.

