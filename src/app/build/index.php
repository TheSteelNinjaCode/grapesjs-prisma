<?php

use Lib\Prisma\Classes\Prisma;
use Lib\Request;

function slugify(string $text): string
{
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $text) ?? '');
    $text = trim($text, '-');
    return $text !== '' ? $text : 'page';
}

/**
 * Ensure slug is unique per project.
 * If $existingSlug is passed, keep it stable (don’t change on rename).
 */
function ensureUniqueSlug($prisma, string $projectId, string $baseSlug, ?string $existingSlug = null): string
{
    if ($existingSlug) return $existingSlug;

    $slug    = $baseSlug;
    $suffix  = 2;
    while (true) {
        $found = $prisma->studioPage->findFirst([
            'where' => [
                'projectId' => $projectId,
                'slug'      => $slug,
            ],
            'select' => ['id' => true],
        ]);
        if (!$found) return $slug;
        $slug = $baseSlug . '-' . $suffix++;
    }
}

function saveProject(object $data)
{
    $prisma = Prisma::getInstance();

    $name        = $data->pages[0]->name        ?? 'Untitled';
    $projectKey  = $data->project_key           ?? null;
    $projectType = $data->custom->projectType   ?? null;
    $exports     = $data->exports ?? [];

    // keep the body-only html/css for backward-compat (first page)
    $firstExport = is_array($exports) ? ($exports[0] ?? null) : null;
    $html = is_array($firstExport)  ? ($firstExport['html'] ?? null)
        : (is_object($firstExport) ? ($firstExport->html ?? null) : null);
    $css  = is_array($firstExport)  ? ($firstExport['css']  ?? null)
        : (is_object($firstExport) ? ($firstExport->css  ?? null) : null);

    // Strip large/transient fields from stored JSON
    unset($data->callback);
    unset($data->exports);

    // Upsert project
    $project = $prisma->studioProject->upsert([
        'where'  => ['projectKey' => $projectKey],
        'update' => [
            'name'        => $name,
            'projectType' => $projectType,
            'data'        => $data,
            'html'        => $html,
            'css'         => $css,
        ],
        'create' => [
            'name'        => $name,
            'projectKey'  => $projectKey,
            'projectType' => $projectType,
            'data'        => $data,
            'html'        => $html,
            'css'         => $css,
        ],
        'select' => ['id' => true],
    ]);

    $projectId = $project->id;

    // Normalize incoming exports
    $incoming = [];
    if (is_array($exports)) {
        $i = 0;
        foreach ($exports as $exp) {
            $isObj = is_object($exp);
            $isArr = is_array($exp);
            $incoming[] = [
                'pageId' => $isArr ? ($exp['id']   ?? null) : ($isObj ? ($exp->id   ?? null) : null),
                'name'   => $isArr ? ($exp['name'] ?? 'Untitled') : ($isObj ? ($exp->name ?? 'Untitled') : 'Untitled'),
                'html'   => $isArr ? ($exp['html'] ?? '') : ($isObj ? ($exp->html ?? '') : ''),
                'css'    => $isArr ? ($exp['css']  ?? null) : ($isObj ? ($exp->css ?? null) : null),
                'sort'   => $i++,
            ];
        }
    }

    // Fetch existing pages for sync
    $existingPages = $prisma->studioPage->findMany([
        'where'  => ['projectId' => $projectId],
        'select' => ['id' => true, 'pageId' => true, 'slug' => true],
    ]);

    $byPageId = [];
    foreach ($existingPages as $p) $byPageId[$p->pageId] = $p;

    // Delete pages that no longer exist in the project
    $incomingIds = array_column($incoming, 'pageId');
    foreach ($existingPages as $p) {
        if (!in_array($p->pageId, $incomingIds, true)) {
            $prisma->studioPage->delete([
                'where' => ['id' => $p->id],
            ]);
        }
    }

    // Determine home page (first in GrapesJS pages list)
    $homePageId = $incoming[0]['pageId'] ?? null;

    // Upsert all incoming pages
    foreach ($incoming as $item) {
        $pageId = (string)$item['pageId'];
        $name   = (string)$item['name'];
        $baseSlug = slugify($name);

        if (isset($byPageId[$pageId])) {
            // Update existing (keep slug stable)
            $existing = $byPageId[$pageId];
            $prisma->studioPage->update([
                'where'  => ['id' => $existing->id],
                'data'   => [
                    'name'   => $name,
                    'html'   => $item['html'],
                    'css'    => $item['css'],
                    'sort'   => $item['sort'],
                    'isHome' => $pageId === $homePageId,
                ],
            ]);
        } else {
            // Create new with unique slug
            $slug = ensureUniqueSlug($prisma, $projectId, $baseSlug, null);
            $prisma->studioPage->create([
                'data' => [
                    'projectId' => $projectId,
                    'pageId'    => $pageId,
                    'name'      => $name,
                    'slug'      => $slug,
                    'html'      => $item['html'],
                    'css'       => $item['css'],
                    'sort'      => $item['sort'],
                    'isHome'    => $pageId === $homePageId,
                ],
            ]);
        }
    }

    return ['ok' => true];
}

function loadProject(): array
{
    $prisma  = Prisma::getInstance();
    $project = $prisma->studioProject->findUnique([
        'where' => ['projectKey' => 'demo-project'],
        'select' => ['data' => true],
    ]);

    return ['project' => $project?->data ? json_decode($project->data, true) : []];
}

?>

<style>
    html,
    body {
        margin: 0;
        /* remove browser default margin */
        padding: 0;
        /* remove browser default padding */
        height: 100%;
        /* ensure full height */
        width: 100%;
        /* ensure full width */
        overflow: hidden;
        /* prevent scrollbars */
    }

    #gjs {
        height: 100vh;
        /* full viewport height */
        width: 100vw;
        /* full viewport width */
        box-sizing: border-box;
    }
</style>

<div id="gjs"></div>

<script type="module">
    import createStudioEditor from '@grapesjs/studio-sdk';

    const PROJECT_ID = 'demo-project';
    let editor;

    const collectExports = () => {
        const pages = editor.Pages.getAll();
        return pages.map((p, i) => {
            const component = p.getMainComponent();
            return {
                id: p.getId(),
                name: p.getName(),
                sort: i,
                // Export HTML/CSS scoped to THIS page
                html: editor.getHtml({
                    component
                }),
                css: editor.getCss({
                    component
                }),
            };
        });
    };

    const save = async project => {
        const exports = collectExports();
        await pphp.fetchFunction('saveProject', {
            ...project,
            exports,
            project_key: PROJECT_ID,
        });
    };

    const load = async () => {
        const {
            response
        } = await pphp.fetchFunction('loadProject');
        return response?.project ? {
            project: response.project
        } : {};
    };

    createStudioEditor({
        root: '#gjs',
        project: {
            type: 'web',
            id: PROJECT_ID
        },
        storage: {
            type: 'self',
            autosaveChanges: 5,
            onSave: async ({
                project
            }) => save(project),
            onLoad: async () => load(),
        },
        onReady: ed => {
            editor = ed;
        }
    });
</script>