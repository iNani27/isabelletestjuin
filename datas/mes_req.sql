SELECT  u.id, u.lemail, u.lenom, 
		d.lenom AS nom_perm, d.laperm 
	FROM utilisateur u
		INNER JOIN droit d ON u.droit_id = d.id
    WHERE u.lelogin='admin' AND u.lepass = 'admin';
    
SELECT u.lenom, p.*, GROUP_CONCAT(r.id) AS idrub, GROUP_CONCAT(r.lintitule SEPARATOR '|||' ) AS lintitule
    FROM photo p
    LEFT JOIN photo_has_rubriques h ON h.photo_id = p.id
    LEFT JOIN rubriques r ON h.rubriques_id = r.id
    INNER JOIN utilisateur u 
        GROUP BY p.id
        ORDER BY p.id DESC
        
        ;
        
SELECT p.lenom,p.lextention,p.letitre,p.ladedsc, u.lelogin, 
    GROUP_CONCAT(r.id) AS rubid, 
    GROUP_CONCAT(r.lintitule SEPARATOR '~~') AS lintitule 
    FROM photo p
    INNER JOIN utilisateur u ON u.id = p.utilisateur_id
    LEFT JOIN photo_has_rubriques h ON h.photo_id = p.id
    LEFT JOIN rubriques r ON h.rubriques_id = r.id
    
    GROUP BY p.id
    ORDER BY p.id DESC;
    
SELECT p.lenom,p.lextention,p.letitre,p.ladedsc, u.lelogin, 
    GROUP_CONCAT(r.id) AS rubid, 
    GROUP_CONCAT(r.lintitule SEPARATOR '~~') AS lintitule 
    FROM photo p
    INNER JOIN utilisateur u ON u.id = p.utilisateur_id
    LEFT JOIN photo_has_rubriques h ON h.photo_id = p.id
    LEFT JOIN rubriques r ON h.rubriques_id = r.id
    WHERE p.utilisateur_id = ".$_SESSION['id']."
    GROUP BY p.id
    ORDER BY p.id DESC