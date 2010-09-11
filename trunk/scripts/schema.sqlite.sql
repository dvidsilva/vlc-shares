CREATE TABLE plg_megavideo (
    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    idVideo VARCHAR(255) NOT NULL,
    category VARCHAR(255) NOT NULL DEFAULT 'default',
    label VARCHAR(255) NOT NULL,
    description TEXT NULL
);
 
CREATE INDEX "id" ON "plg_megavideo" ("id");


