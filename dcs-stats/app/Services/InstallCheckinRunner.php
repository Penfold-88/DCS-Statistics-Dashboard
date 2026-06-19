<?php

namespace DcsStats\Services;

final class InstallCheckinRunner
{
    private InstallCheckinStateStore $stateStore;
    private InstallCheckinConfiguration $configuration;
    private InstallCheckinPayloadBuilder $payloadBuilder;
    private InstallCheckinSchedulePolicy $schedulePolicy;
    private \Closure $sendPayload;

    public function __construct(
        InstallCheckinStateStore $stateStore,
        InstallCheckinConfiguration $configuration,
        InstallCheckinPayloadBuilder $payloadBuilder,
        InstallCheckinSchedulePolicy $schedulePolicy,
        callable $sendPayload
    ) {
        $this->stateStore = $stateStore;
        $this->configuration = $configuration;
        $this->payloadBuilder = $payloadBuilder;
        $this->schedulePolicy = $schedulePolicy;
        $this->sendPayload = \Closure::fromCallable($sendPayload);
    }

    public function run(array $versionInfo = [], array $updateChannel = [], array $options = []): array
    {
        $endpoint = $this->configuration->endpoint();
        if ($endpoint === '') {
            return ['status' => 'not_configured'];
        }
        if (!preg_match('#^https?://#i', $endpoint)) {
            return ['status' => 'invalid_endpoint'];
        }

        $today = gmdate('Y-m-d');
        $state = $this->stateStore->load();
        $payload = $this->payloadBuilder->checkinPayload($versionInfo, $updateChannel, $options, $today);
        $payloadKey = $this->payloadBuilder->key($payload);
        $force = !empty($options['force']);
        $skipStatus = $this->schedulePolicy->skipStatus($state, $payloadKey, $today, $force, time());
        if ($skipStatus !== null) {
            return ['status' => $skipStatus];
        }

        $state['last_attempt_day'] = $today;
        $state['last_attempt_at'] = gmdate('c');
        $state['last_attempt_key'] = $payloadKey;

        if (($this->sendPayload)($endpoint, $payload)) {
            $state['last_success_day'] = $today;
            $state['last_success_at'] = gmdate('c');
            $state['last_success_key'] = $payloadKey;
            $state['last_payload'] = $payload;
            unset($state['install_id']);
            $this->stateStore->save($state);

            return ['status' => 'sent'];
        }

        unset($state['install_id']);
        $this->stateStore->save($state);

        return ['status' => 'failed'];
    }
}
