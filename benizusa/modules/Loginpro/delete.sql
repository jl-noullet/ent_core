/**
 * Delete SQL
 *
 * Required if install.sql file present
 * - Delete profile exceptions
 * - Delete program config options if any (to every schools)
 * - Delete module specific tables
 * (and their eventual sequences & indexes) if any
 */

--
-- Delete from profile_exceptions table
--

DELETE FROM profile_exceptions WHERE modname='Loginpro/ListeEleves.php';
