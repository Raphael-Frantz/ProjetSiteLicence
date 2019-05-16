USE `linfol2`;

/**
 * Table `inf_epreuve_planning`
 * Contient la planification des examens terminaux
 */
DROP TABLE IF EXISTS `inf_epreuve_date`;
CREATE TABLE IF NOT EXISTS `inf_epreuve_date` (
  `eprp_id` int(11) NOT NULL AUTO_INCREMENT, /* ID de la planification */
  `eprp_debut` int(11) NOT NULL,             /* Timestamp du début de l'épreuve */
  `eprp_fin` int(11) NOT NULL,               /* Timestamp de la fin de l'épreuve */
  `eprp_epreuve` int(11) NOT NULL,           /* ID de l'épreuve associée (inf_epreuve.epr_id) */
  `eprp_groupe` int(11) NOT NULL,             /* ID du groupe associé à la date de l'épreuve (inf_groupe.gro_id) */
  PRIMARY KEY (`eprp_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;