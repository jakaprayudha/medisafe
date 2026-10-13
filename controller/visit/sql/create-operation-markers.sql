CREATE TABLE IF NOT EXISTS operation_markers (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_customer INT NOT NULL,
    visit_ID VARCHAR(50) NOT NULL,
    marker_type VARCHAR(20) NOT NULL,
    annotations LONGTEXT NOT NULL,
    image_png MEDIUMBLOB NOT NULL,
    notes TEXT NOT NULL,
    created_by_id VARCHAR(100) NOT NULL,
    created_by VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY visit_versions (id_customer, visit_ID, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
