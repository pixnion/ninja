-- Modify saved sla reports tables to case sensitive

ALTER TABLE sla_config ADD cluster_mode INT NOT NULL DEFAULT 0;
