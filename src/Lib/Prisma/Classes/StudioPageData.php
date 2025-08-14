<?php

declare(strict_types=1);

namespace Lib\Prisma\Classes;

use DateTime;

class StudioPageData
{

    public ?StudioPageData $_avg = null;
    public ?StudioPageData $_count = null;
    public ?StudioPageData $_max = null;
    public ?StudioPageData $_min = null;
    public ?StudioPageData $_sum = null;
    public ?string $id;
    public string $projectId;
    public string $pageId;
    public string $name;
    public string $slug;
    public string $html;
    public ?string $css;
    public bool $isHome;
    public int $sort;
    public DateTime|string $createdAt;
    public DateTime|string $updatedAt;
    public ?StudioProjectData $project;

    public function __construct(
        string $projectId,
        string $pageId,
        string $name,
        string $slug,
        string $html,
        bool $isHome = false,
        int $sort = 0,
        DateTime|string $createdAt = new DateTime(),
        DateTime|string $updatedAt = new DateTime(),
        ?string $id = null,
        ?string $css = null,
        ?StudioProjectData $project = null,
    ) {
        $this->id = $id;
        $this->projectId = $projectId;
        $this->pageId = $pageId;
        $this->name = $name;
        $this->slug = $slug;
        $this->html = $html;
        $this->css = $css;
        $this->isHome = $isHome;
        $this->sort = $sort;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->project = $project;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'projectId' => $this->projectId,
            'pageId' => $this->pageId,
            'name' => $this->name,
            'slug' => $this->slug,
            'html' => $this->html,
            'css' => $this->css,
            'isHome' => $this->isHome,
            'sort' => $this->sort,
            'createdAt' => $this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : null,
            'updatedAt' => $this->updatedAt ? $this->updatedAt->format('Y-m-d H:i:s') : null,
            'project' => $this->project
        ];
    }
}