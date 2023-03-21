<?php

declare(strict_types=1);

namespace Swew\Framework\Hook;

enum HK
{
    case beforeInit;
    case beforeRun;
    case beforeHandlePipeline;
    case onProcess;
    case afterHandlePipeline;
    case beforeSend;
    case afterSend;
    case onError;
}
