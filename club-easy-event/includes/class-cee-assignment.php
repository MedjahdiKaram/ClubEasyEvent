<?php
/**
 * Player/team assignment utilities.
 *
 * @package ClubEasyEvent\Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

/**
 * Maintains relationships between players and teams.
 */
class CEE_Assignment {

        /**
         * Register admin hooks.
         *
         * @param CEE_Loader $loader Loader instance.
         *
         * @return void
         */
        public function register_admin_hooks( CEE_Loader $loader ) {
                $loader->add_action( 'save_post_cee_team', $this, 'after_team_save', 20, 2 );
                $loader->add_action( 'save_post_cee_player', $this, 'after_player_save', 20, 2 );
        }

        /**
         * Sync relationships after team save.
         *
         * @param int     $post_id Post ID.
         * @param WP_Post $post    Post object.
         *
         * @return void
         */
        public function after_team_save( $post_id, $post ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
                $player_ids = array_map( 'absint', (array) get_post_meta( $post_id, '_cee_team_players', true ) );
                $player_ids = array_values( array_unique( array_filter( $player_ids ) ) );
                $this->sync_team_players( $post_id, $player_ids );
        }

        /**
         * Sync relationships after player save.
         *
         * @param int     $post_id Post ID.
         * @param WP_Post $post    Post object.
         *
         * @return void
         */
        public function after_player_save( $post_id, $post ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
                $team_ids = array_map( 'absint', (array) get_post_meta( $post_id, '_cee_player_teams', true ) );
                $team_ids = array_values( array_unique( array_filter( $team_ids ) ) );
                $this->sync_player_teams( $post_id, $team_ids );
        }

        /**
         * Sync data from team perspective.
         *
         * @param int   $team_id    Team ID.
         * @param array $player_ids Player IDs.
         *
         * @return void
         */
        public function sync_team_players( $team_id, array $player_ids ) {
                $player_ids = array_values( array_unique( array_filter( array_map( 'absint', $player_ids ) ) ) );
                $stored     = array_values( array_unique( array_filter( array_map( 'absint', (array) get_post_meta( $team_id, '_cee_team_players', true ) ) ) ) );

                if ( $player_ids !== $stored ) {
                        update_post_meta( $team_id, '_cee_team_players', $player_ids );
                }

                $all_players = array_unique( array_merge( $player_ids, $stored ) );
                foreach ( $all_players as $player_id ) {
                        $teams    = array_values( array_unique( array_filter( array_map( 'absint', (array) get_post_meta( $player_id, '_cee_player_teams', true ) ) ) ) );
                        $original = $teams;

                        if ( in_array( $player_id, $player_ids, true ) ) {
                                if ( ! in_array( $team_id, $teams, true ) ) {
                                        $teams[] = $team_id;
                                }
                        } else {
                                $teams = array_diff( $teams, array( $team_id ) );
                        }

                        $teams = array_values( array_unique( array_filter( array_map( 'absint', $teams ) ) ) );
                        if ( $teams !== $original ) {
                                update_post_meta( $player_id, '_cee_player_teams', $teams );
                                do_action( 'cee_player_team_assignment_changed', $player_id, $teams, $team_id );
                        }
                }
        }

        /**
         * Sync data from player perspective.
         *
         * @param int   $player_id Player ID.
         * @param array $team_ids  Team IDs.
         *
         * @return void
         */
        public function sync_player_teams( $player_id, array $team_ids ) {
                $team_ids = array_values( array_unique( array_filter( array_map( 'absint', $team_ids ) ) ) );
                $stored   = array_values( array_unique( array_filter( array_map( 'absint', (array) get_post_meta( $player_id, '_cee_player_teams', true ) ) ) ) );

                if ( $team_ids !== $stored ) {
                        update_post_meta( $player_id, '_cee_player_teams', $team_ids );
                        do_action( 'cee_player_team_assignment_changed', $player_id, $team_ids, 0 );
                }

                $all_teams = array_unique( array_merge( $team_ids, $stored ) );
                foreach ( $all_teams as $team_id ) {
                        $players  = array_values( array_unique( array_filter( array_map( 'absint', (array) get_post_meta( $team_id, '_cee_team_players', true ) ) ) ) );
                        $original = $players;

                        if ( in_array( $team_id, $team_ids, true ) ) {
                                if ( ! in_array( $player_id, $players, true ) ) {
                                        $players[] = $player_id;
                                }
                        } else {
                                $players = array_diff( $players, array( $player_id ) );
                        }

                        $players = array_values( array_unique( array_filter( array_map( 'absint', $players ) ) ) );
                        if ( $players !== $original ) {
                                update_post_meta( $team_id, '_cee_team_players', $players );
                        }
                }
        }

        /**
         * Retrieve players for UI dropdowns.
         *
         * @return array
         */
        public function get_players_for_assignment() {
                $query = new WP_Query(
                        array(
                                'post_type'      => 'cee_player',
                                'post_status'    => array( 'publish', 'draft', 'pending' ),
                                'posts_per_page' => 200,
                                'orderby'        => 'title',
                                'order'          => 'ASC',
                                'fields'         => 'ids',
                        )
                );

                $players = array();
                foreach ( $query->posts as $player_id ) {
                        $players[ $player_id ] = get_the_title( $player_id );
                }

                return $players;
        }

        /**
         * Retrieve teams for UI dropdowns.
         *
         * @return array
         */
        public function get_teams_for_assignment() {
                $query = new WP_Query(
                        array(
                                'post_type'      => 'cee_team',
                                'post_status'    => array( 'publish', 'draft', 'pending' ),
                                'posts_per_page' => 200,
                                'orderby'        => 'title',
                                'order'          => 'ASC',
                                'fields'         => 'ids',
                        )
                );

                $teams = array();
                foreach ( $query->posts as $team_id ) {
                        $teams[ $team_id ] = get_the_title( $team_id );
                }

                return $teams;
        }
}
