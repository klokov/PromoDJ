<?php defined('SYSPATH') or die('No direct script access.');

class Model_Tracks extends Model
{

    public function save_tracks ($tracks) {
        $res = array('added' => array(), 'exist' => array());
        $qre_sel = DB::query(Database::SELECT, 'SELECT * FROM tracks WHERE flink = :flink');
        $qre_ins = DB::query(Database::INSERT, 'INSERT INTO tracks ('.
            'status, genre, title, dlink, autor, style, tizer, hinfo, flink, fname, fsize, created) '.
            'VALUES (:status, :genre, :title, :dlink, :autor, :style, :tizer, :hinfo, :flink, :fname, :fsize, :created)');
        foreach ($tracks as $track)
        {
            $qre_sel->param(':flink', $track['flink']);
            if ($qre_sel->execute()->count() == 0) {
                $qre_ins->param(':status', 'parsed');
                $qre_ins->param(':genre', $track['genre']);
                $qre_ins->param(':title', $track['title']);
                $qre_ins->param(':dlink', $track['dlink']);
                $qre_ins->param(':autor', $track['autor']);
                $qre_ins->param(':style', $track['style']);
                $qre_ins->param(':tizer', $track['tizer']);
                $qre_ins->param(':hinfo', $track['hinfo']);
                $qre_ins->param(':flink', $track['flink']);
                $qre_ins->param(':fname', $track['fname']);
                $qre_ins->param(':fsize', $track['fsize']);
                $qre_ins->param(':created', date('Y-m-d H:i:s'));
                $qre_ins->execute();
                $res['added'][] = $track;
            }
            else
            {
                $res['exist'][] = $track;
            }
        }
        return $res;
    }

    public function get_all()
    {
        return DB::query(Database::SELECT, "SELECT * FROM tracks LIMIT 100")
            ->execute();
    }

    public function get_status($status, $limit) {
        return DB::query(Database::SELECT, "SELECT id, status, fname, flink FROM tracks WHERE status = :status LIMIT :limit")
            ->param(':status', $status)
            ->param(':limit', $limit)
            ->execute();
    }

    public function set_status ($tracks, $status) {
        $qre_upd = DB::query(Database::UPDATE, 'UPDATE tracks SET status = :status WHERE id = :id')
            ->param(':status', $status);
        foreach ($tracks as $track)
        {
            $qre_upd->param(':id', $track['id'])
                ->execute();
        }
    }


}