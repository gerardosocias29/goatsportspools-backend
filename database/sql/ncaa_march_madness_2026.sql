-- ============================================================
-- NCAA March Madness 2026 - 64 Teams Insert
-- Based on Lunardi Bracketology projection (March 12, 2026)
-- UPDATE after Selection Sunday (March 15, 2026) with official bracket
-- ============================================================

-- ============================================================
-- STEP 1: Soft-delete ALL old ncaa_teams (keeps old auction references intact)
-- ============================================================
UPDATE ncaa_teams SET deleted_at = NOW() WHERE deleted_at IS NULL;


-- ============================================================
-- STEP 2: Insert all 64 NEW teams for 2026 March Madness
-- ============================================================
INSERT INTO ncaa_teams (school, nickname, created_at, updated_at) VALUES

-- EAST REGION (Washington D.C.)
('Duke', 'Blue Devils', NOW(), NOW()),              -- East 1
('Michigan State', 'Spartans', NOW(), NOW()),        -- East 2
('Alabama', 'Crimson Tide', NOW(), NOW()),           -- East 3
('Kansas', 'Jayhawks', NOW(), NOW()),                -- East 4
('Vanderbilt', 'Commodores', NOW(), NOW()),          -- East 5
('North Carolina', 'Tar Heels', NOW(), NOW()),       -- East 6
('Villanova', 'Wildcats', NOW(), NOW()),             -- East 7
('Utah State', 'Aggies', NOW(), NOW()),              -- East 8
('Iowa', 'Hawkeyes', NOW(), NOW()),                  -- East 9
('Santa Clara', 'Broncos', NOW(), NOW()),            -- East 10
('UCF', 'Knights', NOW(), NOW()),                    -- East 11 *First Four
('High Point', 'Panthers', NOW(), NOW()),            -- East 12
('Hofstra', 'Pride', NOW(), NOW()),                  -- East 13
('Troy', 'Trojans', NOW(), NOW()),                   -- East 14
('UMBC', 'Retrievers', NOW(), NOW()),                -- East 15
('Howard', 'Bison', NOW(), NOW()),                   -- East 16 *First Four

-- SOUTH REGION (Houston)
('Florida', 'Gators', NOW(), NOW()),                 -- South 1
('Houston', 'Cougars', NOW(), NOW()),                -- South 2
('Nebraska', 'Cornhuskers', NOW(), NOW()),           -- South 3
('Purdue', 'Boilermakers', NOW(), NOW()),            -- South 4
('St. John''s', 'Red Storm', NOW(), NOW()),          -- South 5
('Louisville', 'Cardinals', NOW(), NOW()),           -- South 6
('Saint Mary''s', 'Gaels', NOW(), NOW()),            -- South 7
('Miami', 'Hurricanes', NOW(), NOW()),               -- South 8
('TCU', 'Horned Frogs', NOW(), NOW()),               -- South 9
('Texas', 'Longhorns', NOW(), NOW()),                -- South 10
('Missouri', 'Tigers', NOW(), NOW()),                -- South 11
('USF', 'Bulls', NOW(), NOW()),                      -- South 12
('Liberty', 'Flames', NOW(), NOW()),                 -- South 13
('Wright State', 'Raiders', NOW(), NOW()),           -- South 14
('Furman', 'Paladins', NOW(), NOW()),                -- South 15
('Bethune-Cookman', 'Wildcats', NOW(), NOW()),       -- South 16 *First Four

-- MIDWEST REGION (Chicago)
('Michigan', 'Wolverines', NOW(), NOW()),            -- Midwest 1
('UConn', 'Huskies', NOW(), NOW()),                  -- Midwest 2
('Iowa State', 'Cyclones', NOW(), NOW()),            -- Midwest 3
('Texas Tech', 'Red Raiders', NOW(), NOW()),         -- Midwest 4
('Tennessee', 'Volunteers', NOW(), NOW()),           -- Midwest 5
('Wisconsin', 'Badgers', NOW(), NOW()),              -- Midwest 6
('Georgia', 'Bulldogs', NOW(), NOW()),               -- Midwest 7
('Ohio State', 'Buckeyes', NOW(), NOW()),            -- Midwest 8
('Clemson', 'Tigers', NOW(), NOW()),                 -- Midwest 9
('NC State', 'Wolfpack', NOW(), NOW()),              -- Midwest 10
('SMU', 'Mustangs', NOW(), NOW()),                   -- Midwest 11 *First Four
('Northern Iowa', 'Panthers', NOW(), NOW()),         -- Midwest 12
('Utah Valley', 'Wolverines', NOW(), NOW()),         -- Midwest 13
('North Dakota State', 'Bison', NOW(), NOW()),       -- Midwest 14
('Queens', 'Royals', NOW(), NOW()),                  -- Midwest 15
('Long Island', 'Sharks', NOW(), NOW()),             -- Midwest 16

-- WEST REGION (San Jose)
('Arizona', 'Wildcats', NOW(), NOW()),               -- West 1
('Illinois', 'Fighting Illini', NOW(), NOW()),       -- West 2
('Gonzaga', 'Bulldogs', NOW(), NOW()),               -- West 3
('Virginia', 'Cavaliers', NOW(), NOW()),             -- West 4
('Arkansas', 'Razorbacks', NOW(), NOW()),            -- West 5
('BYU', 'Cougars', NOW(), NOW()),                    -- West 6
('Kentucky', 'Wildcats', NOW(), NOW()),              -- West 7
('UCLA', 'Bruins', NOW(), NOW()),                    -- West 8
('Texas A&M', 'Aggies', NOW(), NOW()),               -- West 9
('Saint Louis', 'Billikens', NOW(), NOW()),          -- West 10
('Miami (OH)', 'RedHawks', NOW(), NOW()),            -- West 11
('Yale', 'Bulldogs', NOW(), NOW()),                  -- West 12
('SF Austin', 'Lumberjacks', NOW(), NOW()),          -- West 13
('UC Irvine', 'Anteaters', NOW(), NOW()),            -- West 14
('Tennessee State', 'Tigers', NOW(), NOW()),         -- West 15
('Siena', 'Saints', NOW(), NOW());                   -- West 16


-- ============================================================
-- STEP 3: Insert auction_items for a specific auction
-- Replace {AUCTION_ID} with the actual auction ID
-- Uses only the NEW teams (deleted_at IS NULL)
-- ============================================================

INSERT INTO auction_items (auction_id, ncaa_team_id, name, description, region, seed, status, starting_bid, minimum_bid, created_at, updated_at)
SELECT {AUCTION_ID}, id, school, CONCAT('(East 1) ', school), 'East', 1, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Duke' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(East 2) ', school), 'East', 2, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Michigan State' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(East 3) ', school), 'East', 3, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Alabama' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(East 4) ', school), 'East', 4, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Kansas' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(East 5) ', school), 'East', 5, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Vanderbilt' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(East 6) ', school), 'East', 6, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'North Carolina' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(East 7) ', school), 'East', 7, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Villanova' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(East 8) ', school), 'East', 8, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Utah State' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(East 9) ', school), 'East', 9, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Iowa' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(East 10) ', school), 'East', 10, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Santa Clara' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(East 11) ', school), 'East', 11, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'UCF' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(East 12) ', school), 'East', 12, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'High Point' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(East 13) ', school), 'East', 13, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Hofstra' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(East 14) ', school), 'East', 14, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Troy' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(East 15) ', school), 'East', 15, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'UMBC' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(East 16) ', school), 'East', 16, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Howard' AND deleted_at IS NULL

UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(South 1) ', school), 'South', 1, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Florida' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(South 2) ', school), 'South', 2, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Houston' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(South 3) ', school), 'South', 3, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Nebraska' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(South 4) ', school), 'South', 4, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Purdue' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(South 5) ', school), 'South', 5, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'St. John''s' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(South 6) ', school), 'South', 6, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Louisville' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(South 7) ', school), 'South', 7, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Saint Mary''s' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(South 8) ', school), 'South', 8, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Miami' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(South 9) ', school), 'South', 9, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'TCU' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(South 10) ', school), 'South', 10, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Texas' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(South 11) ', school), 'South', 11, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Missouri' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(South 12) ', school), 'South', 12, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'USF' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(South 13) ', school), 'South', 13, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Liberty' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(South 14) ', school), 'South', 14, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Wright State' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(South 15) ', school), 'South', 15, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Furman' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(South 16) ', school), 'South', 16, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Bethune-Cookman' AND deleted_at IS NULL

UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(Midwest 1) ', school), 'Midwest', 1, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Michigan' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(Midwest 2) ', school), 'Midwest', 2, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'UConn' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(Midwest 3) ', school), 'Midwest', 3, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Iowa State' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(Midwest 4) ', school), 'Midwest', 4, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Texas Tech' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(Midwest 5) ', school), 'Midwest', 5, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Tennessee' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(Midwest 6) ', school), 'Midwest', 6, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Wisconsin' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(Midwest 7) ', school), 'Midwest', 7, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Georgia' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(Midwest 8) ', school), 'Midwest', 8, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Ohio State' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(Midwest 9) ', school), 'Midwest', 9, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Clemson' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(Midwest 10) ', school), 'Midwest', 10, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'NC State' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(Midwest 11) ', school), 'Midwest', 11, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'SMU' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(Midwest 12) ', school), 'Midwest', 12, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Northern Iowa' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(Midwest 13) ', school), 'Midwest', 13, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Utah Valley' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(Midwest 14) ', school), 'Midwest', 14, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'North Dakota State' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(Midwest 15) ', school), 'Midwest', 15, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Queens' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(Midwest 16) ', school), 'Midwest', 16, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Long Island' AND deleted_at IS NULL

UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(West 1) ', school), 'West', 1, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Arizona' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(West 2) ', school), 'West', 2, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Illinois' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(West 3) ', school), 'West', 3, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Gonzaga' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(West 4) ', school), 'West', 4, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Virginia' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(West 5) ', school), 'West', 5, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Arkansas' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(West 6) ', school), 'West', 6, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'BYU' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(West 7) ', school), 'West', 7, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Kentucky' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(West 8) ', school), 'West', 8, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'UCLA' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(West 9) ', school), 'West', 9, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Texas A&M' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(West 10) ', school), 'West', 10, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Saint Louis' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(West 11) ', school), 'West', 11, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Miami (OH)' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(West 12) ', school), 'West', 12, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Yale' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(West 13) ', school), 'West', 13, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'SF Austin' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(West 14) ', school), 'West', 14, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'UC Irvine' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(West 15) ', school), 'West', 15, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Tennessee State' AND deleted_at IS NULL
UNION ALL SELECT {AUCTION_ID}, id, school, CONCAT('(West 16) ', school), 'West', 16, 'pending', 1.00, 1.00, NOW(), NOW() FROM ncaa_teams WHERE school = 'Siena' AND deleted_at IS NULL
;
