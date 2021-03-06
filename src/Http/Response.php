<?php

declare(strict_types=1);

namespace SWEW\Framework\Http;

use Exception;
use SWEW\Framework\Base\BaseDTO;
use SWEW\Framework\SwewApplication;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class Response extends SymfonyResponse
{
    private SwewApplication $app;

    public RespType $responseType = RespType::HTML;

    private string $featureViewPath = '';

    private string $commonViewPath = '';

    public BaseDTO|array|string|null $rawData = null;

    // []: в зависимости от типа Request - выбирает тип ответа
    // []: если необходимо, то создает viewRenderer
    public function init(SwewApplication $app)
    {
        // TODO: добавить после
        // $this->featureViewPath = $featureViewPath;
        // $this->commonViewPath = $commonViewPath;

        $this->app = $app;
    }

    public function setRawData(BaseDTO|string|array $data): Response
    {
        $this->rawData = $data;

        return $this;
    }

    public function send404($isSendData = true): Response
    {
        $this->setStatusCode(404);
        // TODO: load contend
        $this->setContent('Not found: 404');

        return $this->finalSendResponse($isSendData);
    }

    /**
     * Финальный метод, в нем данные отправляются пользователю
     *
     * @throws Exception
     */
    public function finalSendResponse($isSendData = true): Response
    {
        if (!empty($this->rawData)) {
            if ($this->rawData instanceof BaseDTO) {
                $this->rawData = $this->app->dtoMapper($this->rawData);
            }

            if (is_array($this->rawData)) {
                // Если не заполнили контент до этого, то это JSON
                $this->rawData = $this->ajax($this->rawData);
            }

            $this->setContent($this->rawData);
        }

        if (!$isSendData) {
            return $this;
        }

        return $this->send();
    }

    public function ajax(array $data = []): string
    {
        $this->headers->set('Content-Type', 'application/json');

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    }

    /**
     * Set html template if request type not json
     *
     * @param string $file
     * @param array $data
     * @return Response
     * @throws Exception
     */
    public function view(string $file, array $data = []): static
    {
        if (is_array($this->rawData)) {
            $data = array_merge($this->rawData, $data);
        }

        if ($this->isJson() || $this->responseType === RespType::JSON) {
            return $this->ajax($data);
        }

        $filePathFeature = $this->featureViewPath . DIRECTORY_SEPARATOR . $file;

        if (file_exists($filePathFeature)) {
            $this->rawData = $this->app->renderView($filePathFeature, $data);

            return $this;
        }

        $filePathCommon = $this->commonViewPath . DIRECTORY_SEPARATOR . $file;

        if (file_exists($filePathCommon)) {
            $this->rawData = $this->app->renderView($filePathCommon, $data);

            return $this;
        }

        throw new Exception("Not found view file\n - '{$filePathFeature}'\n - '{$filePathCommon}'");
    }

    // TODO: кажется не нужно
    public function setResponseConfig(array $headerContentTypes): void
    {
        foreach ($headerContentTypes as $type) {
            if ($this->isJson($type)) {
                $this->responseType = RespType::JSON;
            }
        }
    }

    public function isJson(string $headerContentType = ''): bool
    {
        if (empty($headerContentType) && $this->hasHeader('Content-Type')) {
            $headerContentType = $this->headers->get('Content-Type');
        }

        return str_contains($headerContentType, 'json')
            || str_contains($headerContentType, 'javascript');
    }
}
