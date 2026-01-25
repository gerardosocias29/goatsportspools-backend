-- Allow player_id to be null in squares_pool_winners table
-- This allows tracking of unclaimed winning squares
ALTER TABLE squares_pool_winners MODIFY COLUMN player_id BIGINT UNSIGNED NULL;

-- Insert migration record
INSERT INTO migrations (migration, batch) 
VALUES ('2025_12_16_025819_allow_null_player_id_in_squares_pool_winners', (SELECT MAX(batch) + 1 FROM (SELECT batch FROM migrations) AS temp));
