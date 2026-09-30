DROP TABLE IF EXISTS suspects;
DROP TABLE IF EXISTS cases;
DROP TABLE IF EXISTS investigators;

CREATE TABLE investigators (
    investigator_id SERIAL PRIMARY KEY,
    badge_number    VARCHAR(20) UNIQUE NOT NULL,
    full_name       VARCHAR(100) NOT NULL,
    rank            VARCHAR(50) NOT NULL,
    department      VARCHAR(100) NOT NULL,
    phone           VARCHAR(20),
    email           VARCHAR(100) UNIQUE,
    password        VARCHAR(255) NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE cases (
    case_id          SERIAL PRIMARY KEY,
    case_number      VARCHAR(30) UNIQUE NOT NULL,
    title            VARCHAR(200) NOT NULL,
    crime_type       VARCHAR(100) NOT NULL,
    location         VARCHAR(200) NOT NULL,
    incident_date    DATE NOT NULL,
    status           VARCHAR(30) NOT NULL DEFAULT 'Open'
                         CHECK (status IN ('Open', 'Under Investigation', 'Closed')),
    description      TEXT,
    investigator_id  INT NOT NULL REFERENCES investigators(investigator_id)
                         ON DELETE RESTRICT,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE suspects (
    suspect_id            SERIAL PRIMARY KEY,
    full_name             VARCHAR(100) NOT NULL,
    date_of_birth         DATE,
    gender                VARCHAR(10) CHECK (gender IN ('Male', 'Female', 'Other')),
    address               TEXT,
    identification_number VARCHAR(50),
    case_id               INT NOT NULL REFERENCES cases(case_id)
                              ON DELETE RESTRICT,
    created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


INSERT INTO investigators (badge_number, full_name, rank, department, phone, email, password) VALUES
('GPD-001', 'James Gordon',     'Commissioner',    'Major Crimes Unit',     '555-0101', 'gordon@gpd.gotham.gov',  '$2y$10$wK1y30mB84Rj1sHshO9kEu2lIeS5.0U/j2/dZ3gQvL5k01ZJ1pC4e'),
('GPD-002', 'Harvey Bullock',   'Detective',       'Major Crimes Unit',     '555-0102', 'bullock@gpd.gotham.gov', '$2y$10$wK1y30mB84Rj1sHshO9kEu2lIeS5.0U/j2/dZ3gQvL5k01ZJ1pC4e'),
('GPD-003', 'Renee Montoya',    'Detective',       'Special Crimes Unit',   '555-0103', 'montoya@gpd.gotham.gov', '$2y$10$wK1y30mB84Rj1sHshO9kEu2lIeS5.0U/j2/dZ3gQvL5k01ZJ1pC4e'),
('GPD-004', 'Crispus Allen',    'Detective',       'Special Crimes Unit',   '555-0104', 'allen@gpd.gotham.gov',   '$2y$10$wK1y30mB84Rj1sHshO9kEu2lIeS5.0U/j2/dZ3gQvL5k01ZJ1pC4e'),
('GPD-005', 'Sarah Essen',      'Captain',         'Organized Crime Unit',  '555-0105', 'essen@gpd.gotham.gov',   '$2y$10$wK1y30mB84Rj1sHshO9kEu2lIeS5.0U/j2/dZ3gQvL5k01ZJ1pC4e');

INSERT INTO cases (case_number, title, crime_type, location, incident_date, status, description, investigator_id) VALUES
('GTH-2026-001', 'Ace Chemicals Warehouse Break-In',   'Burglary',         'Ace Chemicals, East Gotham',         '2026-01-15', 'Closed',               'Unknown assailant broke into Ace Chemicals storage facility. Several barrels of experimental compounds reported missing.', 1),
('GTH-2026-002', 'Gotham City Bank Heist',             'Armed Robbery',    'Gotham City Bank, Downtown',         '2026-02-03', 'Closed',               'Armed robbery at Gotham City Bank. Perpetrators wore clown masks. Vault accessed using inside knowledge. Estimated loss: $4.2M.', 2),
('GTH-2026-003', 'Narrows Drug Ring Takedown',         'Drug Trafficking', 'The Narrows, Old Gotham',            '2026-02-20', 'Under Investigation',  'Large-scale drug trafficking operation uncovered in the Narrows district. Multiple warehouses identified as distribution hubs.', 3),
('GTH-2026-004', 'Penguin Iceberg Lounge Extortion',   'Extortion',        'Iceberg Lounge, Diamond District',   '2026-03-10', 'Under Investigation',  'Multiple business owners reported threats demanding protection money from an individual known as "The Penguin".', 5),
('GTH-2026-005', 'Blackgate Prison Riot',              'Civil Unrest',     'Blackgate Penitentiary, West Gotham','2026-03-18', 'Closed',               'Major riot erupted in Block D of Blackgate. 3 guards injured, 12 inmates escaped before order was restored.', 1),
('GTH-2026-006', 'Gotham Docks Smuggling Operation',   'Smuggling',        'Port of Gotham, South Docks',        '2026-04-02', 'Under Investigation',  'Customs agents flagged unusual shipping containers arriving from Metropolis. Contents suspected to be illegal weaponry.', 4),
('GTH-2026-007', 'Riddler Museum Theft',               'Grand Theft',      'Gotham Natural History Museum',      '2026-04-25', 'Open',                 'Priceless artifacts stolen from the Egyptian exhibit. Perpetrator left cryptic riddles at the scene for investigators.', 2),
('GTH-2026-008', 'Park Row Homicide',                  'Homicide',         'Park Row, Crime Alley',              '2026-05-01', 'Open',                 'Unidentified male found deceased in Crime Alley. No witnesses. Victim had no identification. Ballistics analysis pending.', 3);

INSERT INTO suspects (full_name, date_of_birth, gender, address, identification_number, case_id) VALUES
('Jack Napier',        '1975-04-01', 'Male',   '221 Monarch Playing Card Co., Gotham', 'GTH-ID-77421', 2),
('Oswald Cobblepot',   '1969-09-19', 'Male',   'Iceberg Lounge, Diamond District',     'GTH-ID-88113', 4),
('Edward Nygma',       '1980-10-02', 'Male',   '14 Puzzle Lane, Midtown Gotham',        'GTH-ID-55678', 7),
('Victor Zsasz',       '1978-03-15', 'Male',   'No Fixed Address, The Narrows',         'GTH-ID-39201', 3),
('Roman Sionis',       '1972-06-08', 'Male',   'Sionis Industries, East Gotham',        'GTH-ID-44501', 6),
('Sal Maroni',         '1965-11-23', 'Male',   'Maroni Restaurant, Little Italy',       'GTH-ID-20034', 3),
('Thomas Blake',       '1983-07-30', 'Male',   'Gotham Zoo Staff Quarters',             'GTH-ID-61780', 8),
('Unknown Subject',    NULL,         'Male',   'Unknown',                               'GTH-ID-UNKN1', 1);
