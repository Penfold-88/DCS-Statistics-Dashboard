<?php

namespace DcsStats\Services;

final class InstallCheckinService
{
    private InstallCheckinStateStore $stateStore;
    private InstallCheckinTransport $transport;
    private InstallCheckinConfiguration $configuration;
    private InstallCheckinPayloadBuilder $payloadBuilder;
    private InstallCheckinSchedulePolicy $schedulePolicy;
    private InstallCheckinRunner $runner;

    public function __construct(
        ?InstallCheckinStateStore $stateStore = null,
        ?InstallCheckinTransport $transport = null,
        ?InstallCheckinConfiguration $configuration = null,
        ?InstallCheckinPayloadBuilder $payloadBuilder = null,
        ?InstallCheckinSchedulePolicy $schedulePolicy = null,
        ?InstallCheckinRunner $runner = null
    ) {
        $this->stateStore = $stateStore ?? new InstallCheckinStateStore();
        $this->transport = $transport ?? new InstallCheckinTransport();
        $this->configuration = $configuration ?? new InstallCheckinConfiguration();
        $this->payloadBuilder = $payloadBuilder ?? new InstallCheckinPayloadBuilder($this->configuration);
        $this->schedulePolicy = $schedulePolicy ?? new InstallCheckinSchedulePolicy();
        $this->runner = $runner ?? new InstallCheckinRunner(
            $this->stateStore,
            $this->configuration,
            $this->payloadBuilder,
            $this->schedulePolicy,
            function (string $endpoint, array $payload): bool {
                return $this->sendPayload($endpoint, $payload);
            }
        );
    }

    public function endpoint(): string
    {
        return $this->configuration->endpoint();
    }

    public function token(): string
    {
        return $this->configuration->token();
    }

    public function allowSslFallback(): bool
    {
        return $this->configuration->allowSslFallback();
    }

    public function statePath(): string
    {
        return $this->stateStore->path();
    }

    public function loadState(): array
    {
        return $this->stateStore->load();
    }

    public function saveState(array $state): void
    {
        $this->stateStore->save($state);
    }

    public function buildPayload(array $payload)
    {
        return $this->payloadBuilder->transportPayload($payload);
    }

    public function sendWithCurl(string $endpoint, string $json, array $headers, bool $verifySsl = true): array
    {
        return $this->transport->sendWithCurl($endpoint, $json, $headers, $verifySsl);
    }

    public function sendPayload(string $endpoint, array $payload): bool
    {
        [$payload, $headers] = $this->buildPayload($payload);
        $json = json_encode($payload);
        if ($json === false) {
            return false;
        }

        return $this->transport->sendPayload($endpoint, $json, $headers, $this->allowSslFallback());
    }

    public function payloadKey(array $payload): string
    {
        return $this->payloadBuilder->key($payload);
    }

    public function runIfDue(array $versionInfo = [], array $updateChannel = [], array $options = []): array
    {
        return $this->runner->run($versionInfo, $updateChannel, $options);
    }
}
