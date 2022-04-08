<?php

declare(strict_types=1);

namespace SWEW\Framework\Http;

use Exception;
use SWEW\Framework\DTO\BaseDTO;
use SWEW\Framework\SwewApplication;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Response extends SymfonyResponse
{
    public RespType $responseType = RespType::JSON;

    private string $featureViewPath = '';

    private string $commonViewPath = '';

    private SwewApplication $app;

    private BaseDTO|array|string|null $data = null;

    // []: в зависимости от типа Request - выбирает тип ответа
    // []: если необходимо, то создает viewRenderer
    public function init(string $featureViewPath, string $commonViewPath, SwewApplication $app)
    {
        $this->featureViewPath = $featureViewPath;
        $this->commonViewPath = $commonViewPath;
        $this->app = $app;
    }

    /**
     *
     *
     * @param array|BaseDTO|null $dto
     * @return $this
     */
    public function res(BaseDTO|array|string $dto = null)
    {
        if ($dto instanceof BaseDTO) {
            $this->data = $dto->getData();
        } else {
            $this->data = $dto;
        }

        return $this;
    }

    public function finalSendResponse(): Response
    {
        // TODO: Раскомментить после добавления контроллеров
        // if (is_null($this->data)) {
        //    throw new Exception('Please set data with: "$this->req(DTO|[]);"');
        // }
        if (is_array($this->data)) {
            // Если не заполнили контент до этого, то это JSON
            $this->json($this->data);
        }

        $this->setContent($this->data);

        return $this->send();
    }

    public function json(array $data = []): static
    {
        $this->data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);

        $this->headers->set('Content-Type', 'application/json');
        return $this;
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
        if (is_array($this->data)) {
            $data = array_merge($this->data, $data);
        }

        if ($this->isJson() || $this->responseType === RespType::JSON) {
            return $this->json($data);
        }

        $filePathFeature = $this->featureViewPath . DIRECTORY_SEPARATOR . $file;

        if (file_exists($filePathFeature)) {
            $this->data = $this->app->renderView($filePathFeature, $data);

            return $this;
        }

        $filePathCommon = $this->commonViewPath . DIRECTORY_SEPARATOR . $file;

        if (file_exists($filePathCommon)) {
            $this->data = $this->app->renderView($filePathCommon, $data);

            return $this;
        }

        throw new Exception("Not found view file\n - '{$filePathFeature}'\n - '{$filePathCommon}'");
    }

    public function setResponseConfig(string $headerContentType): void
    {
        if ($this->isJson($headerContentType)) {
            $this->responseType = RespType::JSON;
        } else {
            $this->responseType = RespType::HTML;
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
