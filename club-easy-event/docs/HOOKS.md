# Hooks

## Filtres
- `cee_schedule_query_args( $args, $atts )`
- `cee_event_shortcode_output( $output, $event_id, $atts )`
- `cee_email_recipients( $emails, $event_id )`
- `cee_primary_color( $hex )`
- `cee_can_approve( $can, $post_id, $state )`
- `cee_event_validation_rules( $rules )`
- `cee_event_update_recipients( $emails, $event_id )`
- `cee_event_update_template( $subject, $body, $event_id, $diff )`
- `cee_player_signup_default_role( $role, $values )`
- `cee_player_signup_default_team_id( $team_id, $player_id, $values )`
- `cee_player_signup_enabled_default( $enabled, $values )`
- `cee_player_signup_success_message( $message, $values )`
- `cee_player_signup_notification_recipients( $emails, $player_id, $user_id, $values )`
- `cee_player_signup_send_confirmation( $send, $player_id, $user_id, $values )`
- `cee_approval_post_types( $post_types )`

## Actions
- `cee_rsvp_updated( $event_id, $user_id, $response )`
- `cee_events_bulk_shift_days( $post_ids, $offset )`
- `cee_player_team_assignment_changed( $player_id, $team_ids )`
- `cee_player_signup_created( $player_id, $user_id, $values )`
- `cee_approval_state_changed( $post_id, $old_state, $new_state )`
