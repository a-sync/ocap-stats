<?php defined('BASEPATH') or exit('*');

class Assorted extends CI_Model
{
    public function __construct()
    {
        $this->load->database();
    }

    public function get_mission_authors($year = false)
    {
        if ($year) {
            $this->db->where('YEAR(operations.start_time)', $year);
        }

        $this->db
            ->select([
                'mission_author',
                'COUNT(mission_author) AS count',
            ])
            ->from('operations')
            ->group_by('mission_author')
            ->order_by('count DESC, mission_author ASC');

        return $this->db->get()->result_array();
    }

    public function get_maps($year = false)
    {
        if ($year) {
            $this->db->where('YEAR(operations.start_time)', $year);
        }

        $this->db
            ->select([
                'world_name',
                'COUNT(world_name) AS count',
            ])
            ->from('operations')
            ->group_by('world_name')
            ->order_by('count DESC, world_name ASC');

        return $this->db->get()->result_array();
    }

    public function get_winners($year = false)
    {
        if ($year) {
            $this->db->where('YEAR(operations.start_time)', $year);
        }

        $this->db
            ->select([
                'end_winner',
                'COUNT(end_winner) AS count',
            ])
            ->from('operations')
            ->group_by('end_winner')
            ->order_by('count DESC, end_winner ASC');

        return $this->db->get()->result_array();
    }

    public function get_end_messages($year = false)
    {
        if ($year) {
            $this->db->where('YEAR(operations.start_time)', $year);
        }

        $this->db
            ->select([
                'end_message',
                'COUNT(end_message) AS count',
            ])
            ->from('operations')
            ->group_by('end_message')
            ->order_by('count DESC, end_message ASC');

        return $this->db->get()->result_array();
    }

    public function get_groups($year = false)
    {
        if ($year) {
            $this->db
                ->join('operations', 'operations.id = entities.operation_id')
                ->where('YEAR(operations.start_time)', $year);
        }

        $this->db
            ->select([
                'group_name',
                'COUNT(group_name) AS count',
            ])
            ->from('entities')
            ->group_by('group_name')
            ->order_by('count DESC, group_name ASC');

        return $this->db->get()->result_array();
    }

    public function get_roles($year = false)
    {
        if ($year) {
            $this->db
                ->join('operations', 'operations.id = entities.operation_id')
                ->where('YEAR(operations.start_time)', $year);
        }

        $this->db
            ->select([
                "TRIM(SUBSTRING_INDEX(role, '@', 1)) AS role_name",
                'COUNT(role) AS count',
            ])
            ->from('entities')
            ->group_by('role_name')
            ->order_by('count DESC, role_name ASC');

        return $this->db->get()->result_array();
    }

    public function get_weapons($year = false)
    {
        if ($year) {
            $this->db
                ->join('operations', 'operations.id = events.operation_id')
                ->where('YEAR(operations.start_time)', $year);
        }

        $this->db
            ->select('weapon')
            ->select_sum("CASE WHEN events.event = 'hit' THEN 1 ELSE NULL END", 'hits')
            ->select_avg("CASE WHEN events.event = 'hit' THEN events.distance ELSE NULL END", 'avg_hit_dist')
            ->select_sum("CASE WHEN events.event = 'killed' THEN 1 ELSE NULL END", 'kills')
            ->select_avg("CASE WHEN events.event = 'killed' THEN events.distance ELSE NULL END", 'avg_kill_dist')
            ->from('events')
            ->where_in('events.event', ['hit', 'killed'])
            ->group_by('weapon')
            ->order_by('kills DESC, hits DESC, weapon ASC');

        return $this->db->get()->result_array();
    }

    public function get_vehicles($year = false)
    {
        if ($year) {
            $this->db
                ->join('operations', 'operations.id = entities.operation_id')
                ->where('YEAR(operations.start_time)', $year);
        }

        $this->db
            ->select([
                'name',
                'class',
                'COUNT(name) AS count',
            ])
            ->from('entities')
            ->where('type', 'vehicle')
            ->group_by('name, class')
            ->order_by('count DESC, class ASC, name ASC');

        return $this->db->get()->result_array();
    }
}
