CREATE TABLE IF NOT EXISTS post_indexes (
    oblast VARCHAR(255) NOT NULL,
    old_district VARCHAR(255),
    new_district VARCHAR(255),
    settlement VARCHAR(255) NOT NULL,
    postal_code VARCHAR(10) NOT NULL PRIMARY KEY,
    region VARCHAR(255) NOT NULL,
    district_new VARCHAR(255),
    settlement_eng VARCHAR(255),
    post_branch VARCHAR(255) NOT NULL,
    post_office VARCHAR(255) NOT NULL,
    post_code_office VARCHAR(10) NOT NULL,
    manual_entry TINYINT(1) DEFAULT 0
    );
