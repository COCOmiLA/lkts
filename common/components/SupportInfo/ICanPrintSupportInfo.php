<?php

namespace common\components\SupportInfo;

interface ICanPrintSupportInfo
{
    public function print(array $params = []): void;

    public function render(array $params = []): string;

    public function showDeveloperInfo(): bool;
}