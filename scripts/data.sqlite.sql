
-- scripts/data.sqlite.sql
--
-- You can begin populating the database with the following SQL statements.
 
INSERT INTO plg_megavideo (idVideo, category, label, description) VALUES
    ('NBDB38CE',
    'BBT',
    'PILOT',
    '');
    
INSERT INTO plg_megavideo (idVideo, category, label, description) VALUES
    ('ZUWSN1B9',
    'BBT',
    'puntata 1',
    '');
    
INSERT INTO plg_megavideo (idVideo, category, label, description) VALUES
    ('T65OW4BR',
    'American Dad',
    'puntata X',
    '');
    
    
INSERT INTO plg_filesystem_shares (label, path ) VALUES
	('Root windows',
	'C:\'
	);

	
INSERT INTO plg_filesystem_shares (label, path) VALUES
	('Root linux',
	'/'
	);