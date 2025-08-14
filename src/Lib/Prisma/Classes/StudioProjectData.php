<?php

declare(strict_types=1);

namespace Lib\Prisma\Classes;

use DateTime;

class StudioProjectData
{

    public ?StudioProjectData $_avg = null;
    public ?StudioProjectData $_count = null;
    public ?StudioProjectData $_max = null;
    public ?StudioProjectData $_min = null;
    public ?StudioProjectData $_sum = null;
    public ?string $id;
    public ?string $name;
    public string $projectKey;
    public ?string $projectType;
    public array|string $data;
    public ?string $html;
    public ?string $css;
    public DateTime|string $createdAt;
    public DateTime|string $updatedAt;
    /** @var StudioPageData[] */
    public ?array $pages;

    public function __construct(
        string $projectKey,
        array|string $data,
        DateTime|string $createdAt = new DateTime(),
        DateTime|string $updatedAt = new DateTime(),
        ?string $id = null,
        ?string $name = null,
        ?string $projectType = null,
        ?string $html = null,
        ?string $css = null,
        ?array $pages = [],
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->projectKey = $projectKey;
        $this->projectType = $projectType;
        $this->data = $data;
        $this->html = $html;
        $this->css = $css;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->pages = $pages;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'projectKey' => $this->projectKey,
            'projectType' => $this->projectType,
            'data' => $this->data,
            'html' => $this->html,
            'css' => $this->css,
            'createdAt' => $this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : null,
            'updatedAt' => $this->updatedAt ? $this->updatedAt->format('Y-m-d H:i:s') : null,
            'pages' => $this->pages
        ];
    }
}