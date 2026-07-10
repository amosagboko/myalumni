USE alumni;

DROP VIEW IF EXISTS `vw_fee_types`;

CREATE OR REPLACE VIEW `vw_fee_types` AS
SELECT
    ft.id,
    ft.name,
    ft.code,
    ft.description,
    ft.is_active,
    ft.is_system,
    ft.created_at,
    ft.updated_at,
    COUNT(DISTINCT vft.id) AS template_count,
    SUM(CASE WHEN vft.is_active = 1 THEN 1 ELSE 0 END) AS active_template_count
FROM fee_types ft
LEFT JOIN vw_fee_templates vft ON ft.id = vft.fee_type_id
GROUP BY
    ft.id,
    ft.name,
    ft.code,
    ft.description,
    ft.is_active,
    ft.is_system,
    ft.created_at,
    ft.updated_at;
