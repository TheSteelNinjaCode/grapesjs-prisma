-- CreateTable
CREATE TABLE "StudioProject" (
    "id" TEXT NOT NULL PRIMARY KEY,
    "name" TEXT,
    "projectKey" TEXT NOT NULL,
    "projectType" TEXT,
    "data" JSONB NOT NULL,
    "html" TEXT,
    "css" TEXT,
    "createdAt" DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updatedAt" DATETIME NOT NULL
);

-- CreateTable
CREATE TABLE "StudioPage" (
    "id" TEXT NOT NULL PRIMARY KEY,
    "projectId" TEXT NOT NULL,
    "pageId" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "slug" TEXT NOT NULL,
    "html" TEXT NOT NULL,
    "css" TEXT,
    "isHome" BOOLEAN NOT NULL DEFAULT false,
    "sort" INTEGER NOT NULL DEFAULT 0,
    "createdAt" DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updatedAt" DATETIME NOT NULL,
    CONSTRAINT "StudioPage_projectId_fkey" FOREIGN KEY ("projectId") REFERENCES "StudioProject" ("id") ON DELETE CASCADE ON UPDATE CASCADE
);

-- CreateIndex
CREATE UNIQUE INDEX "StudioProject_projectKey_key" ON "StudioProject"("projectKey");

-- CreateIndex
CREATE UNIQUE INDEX "StudioPage_projectId_pageId_key" ON "StudioPage"("projectId", "pageId");

-- CreateIndex
CREATE UNIQUE INDEX "StudioPage_projectId_slug_key" ON "StudioPage"("projectId", "slug");
