<?php
/**
 * Player signup shortcode handler.
 *
 * @package ClubEasyEvent\Public
 */

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

/**
 * Renders and processes the player signup form.
 */
class CEE_Shortcode_Player_Signup {

        /**
         * Front-end manager.
         *
         * @var CEE_Frontend
         */
        protected $frontend;

        /**
         * Settings manager.
         *
         * @var CEE_Settings
         */
        protected $settings;

        /**
         * Assignment helper.
         *
         * @var CEE_Assignment
         */
        protected $assignment;

        /**
         * Constructor.
         *
         * @param CEE_Frontend   $frontend   Front-end manager.
         * @param CEE_Settings   $settings   Settings manager.
         * @param CEE_Assignment $assignment Assignment helper.
         */
        public function __construct( CEE_Frontend $frontend, CEE_Settings $settings, CEE_Assignment $assignment ) {
                $this->frontend   = $frontend;
                $this->settings   = $settings;
                $this->assignment = $assignment;
        }

        /**
         * Render shortcode output.
         *
         * @param array $atts Attributes.
         *
         * @return string
         */
        public function render( $atts ) {
                $this->frontend->mark_assets_needed();

                $defaults = array(
                        'first_name'              => '',
                        'last_name'               => '',
                        'email'                   => '',
                        'address_line1'           => '',
                        'address_line2'           => '',
                        'city'                    => '',
                        'postal_code'             => '',
                        'country'                 => '',
                        'phone'                   => '',
                        'emergency_contact_name'  => '',
                        'emergency_contact_phone' => '',
                        'age'                     => '',
                        'note'                    => '',
                );

                $result = $this->maybe_handle_submission( $defaults );

                $errors  = $result['errors'];
                $success = $result['success'];
                $values  = $result['values'];
                $message = $result['message'];

                ob_start();
                ?>
                <div class="cee-signup-wrapper" data-has-success="<?php echo $success ? '1' : '0'; ?>">
                        <?php if ( $message ) : ?>
                                <div class="cee-signup-notice <?php echo $success ? 'cee-signup-notice--success' : 'cee-signup-notice--error'; ?>" role="alert"><?php echo esc_html( $message ); ?></div>
                        <?php endif; ?>
                        <?php if ( ! $success ) : ?>
                        <form method="post" class="cee-signup-form" novalidate>
                                <?php wp_nonce_field( 'cee_player_signup', 'cee_player_signup_nonce' ); ?>
                                <input type="text" name="cee_signup_hp" class="cee-signup-hp" tabindex="-1" autocomplete="off" aria-hidden="true" />
                                <div class="cee-signup-grid">
                                        <div class="cee-signup-field">
                                                <label for="cee_signup_first_name"><?php esc_html_e( 'Prénom', 'club-easy-event' ); ?> <span class="cee-required">*</span></label>
                                                <input type="text" id="cee_signup_first_name" name="cee_signup_first_name" value="<?php echo esc_attr( $values['first_name'] ); ?>" required />
                                                <?php $this->render_field_errors( 'first_name', $errors ); ?>
                                        </div>
                                        <div class="cee-signup-field">
                                                <label for="cee_signup_last_name"><?php esc_html_e( 'Nom', 'club-easy-event' ); ?> <span class="cee-required">*</span></label>
                                                <input type="text" id="cee_signup_last_name" name="cee_signup_last_name" value="<?php echo esc_attr( $values['last_name'] ); ?>" required />
                                                <?php $this->render_field_errors( 'last_name', $errors ); ?>
                                        </div>
                                        <div class="cee-signup-field">
                                                <label for="cee_signup_email"><?php esc_html_e( 'Adresse e-mail', 'club-easy-event' ); ?> <span class="cee-required">*</span></label>
                                                <input type="email" id="cee_signup_email" name="cee_signup_email" value="<?php echo esc_attr( $values['email'] ); ?>" required />
                                                <?php $this->render_field_errors( 'email', $errors ); ?>
                                        </div>
                                        <div class="cee-signup-field">
                                                <label for="cee_signup_age"><?php esc_html_e( 'Âge', 'club-easy-event' ); ?> <span class="cee-required">*</span></label>
                                                <input type="number" id="cee_signup_age" name="cee_signup_age" value="<?php echo esc_attr( $values['age'] ); ?>" min="3" max="120" required />
                                                <p class="description cee-signup-age-note" aria-live="polite"></p>
                                                <?php $this->render_field_errors( 'age', $errors ); ?>
                                        </div>
                                        <div class="cee-signup-field">
                                                <label for="cee_signup_phone"><?php esc_html_e( 'Téléphone', 'club-easy-event' ); ?></label>
                                                <input type="tel" id="cee_signup_phone" name="cee_signup_phone" value="<?php echo esc_attr( $values['phone'] ); ?>" />
                                        </div>
                                        <div class="cee-signup-field">
                                                <label for="cee_signup_address1"><?php esc_html_e( 'Adresse (ligne 1)', 'club-easy-event' ); ?></label>
                                                <input type="text" id="cee_signup_address1" name="cee_signup_address_line1" value="<?php echo esc_attr( $values['address_line1'] ); ?>" />
                                        </div>
                                        <div class="cee-signup-field">
                                                <label for="cee_signup_address2"><?php esc_html_e( 'Adresse (ligne 2)', 'club-easy-event' ); ?></label>
                                                <input type="text" id="cee_signup_address2" name="cee_signup_address_line2" value="<?php echo esc_attr( $values['address_line2'] ); ?>" />
                                        </div>
                                        <div class="cee-signup-field">
                                                <label for="cee_signup_city"><?php esc_html_e( 'Ville', 'club-easy-event' ); ?></label>
                                                <input type="text" id="cee_signup_city" name="cee_signup_city" value="<?php echo esc_attr( $values['city'] ); ?>" />
                                        </div>
                                        <div class="cee-signup-field">
                                                <label for="cee_signup_postal_code"><?php esc_html_e( 'Code postal', 'club-easy-event' ); ?></label>
                                                <input type="text" id="cee_signup_postal_code" name="cee_signup_postal_code" value="<?php echo esc_attr( $values['postal_code'] ); ?>" />
                                        </div>
                                        <div class="cee-signup-field">
                                                <label for="cee_signup_country"><?php esc_html_e( 'Pays', 'club-easy-event' ); ?></label>
                                                <input type="text" id="cee_signup_country" name="cee_signup_country" value="<?php echo esc_attr( $values['country'] ); ?>" />
                                        </div>
                                        <div class="cee-signup-field">
                                                <label for="cee_signup_emergency_name"><?php esc_html_e( 'Contact d’urgence - Nom', 'club-easy-event' ); ?></label>
                                                <input type="text" id="cee_signup_emergency_name" name="cee_signup_emergency_contact_name" value="<?php echo esc_attr( $values['emergency_contact_name'] ); ?>" />
                                        </div>
                                        <div class="cee-signup-field">
                                                <label for="cee_signup_emergency_phone"><?php esc_html_e( 'Contact d’urgence - Téléphone', 'club-easy-event' ); ?></label>
                                                <input type="tel" id="cee_signup_emergency_phone" name="cee_signup_emergency_contact_phone" value="<?php echo esc_attr( $values['emergency_contact_phone'] ); ?>" />
                                        </div>
                                        <div class="cee-signup-field cee-signup-field--wide">
                                                <label for="cee_signup_note"><?php esc_html_e( 'Informations complémentaires', 'club-easy-event' ); ?></label>
                                                <textarea id="cee_signup_note" name="cee_signup_note" rows="3"><?php echo esc_textarea( $values['note'] ); ?></textarea>
                                        </div>
                                </div>
                                <button type="submit" name="cee_player_signup_submit" class="cee-signup-submit"><?php esc_html_e( 'Envoyer ma demande', 'club-easy-event' ); ?></button>
                        </form>
                        <?php endif; ?>
                </div>
                <?php
                return ob_get_clean();
        }

        /**
         * Render field error list.
         *
         * @param string $field  Field key.
         * @param array  $errors Error array.
         *
         * @return void
         */
        protected function render_field_errors( $field, $errors ) {
                if ( empty( $errors[ $field ] ) ) {
                        return;
                }
                echo '<ul class="cee-signup-errors" aria-live="polite">';
                foreach ( $errors[ $field ] as $error ) {
                        echo '<li>' . esc_html( $error ) . '</li>';
                }
                echo '</ul>';
        }

        /**
         * Handle form submission.
         *
         * @param array $defaults Default values.
         *
         * @return array
         */
        protected function maybe_handle_submission( array $defaults ) {
                if ( 'POST' !== $_SERVER['REQUEST_METHOD'] || ! isset( $_POST['cee_player_signup_submit'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Checked below.
                        return array(
                                'success' => false,
                                'errors'  => array(),
                                'values'  => $defaults,
                                'message' => '',
                        );
                }

                if ( ! isset( $_POST['cee_player_signup_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cee_player_signup_nonce'] ) ), 'cee_player_signup' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Sanitized here.
                        return array(
                                'success' => false,
                                'errors'  => array(),
                                'values'  => $defaults,
                                'message' => __( 'La session de sécurité a expiré. Merci de réessayer.', 'club-easy-event' ),
                        );
                }

                if ( ! empty( $_POST['cee_signup_hp'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Honeypot.
                        return array(
                                'success' => false,
                                'errors'  => array(),
                                'values'  => $defaults,
                                'message' => __( 'La soumission n’a pas pu être vérifiée.', 'club-easy-event' ),
                        );
                }

                $values = array();
                foreach ( $defaults as $key => $default ) {
                        $field       = 'cee_signup_' . $key;
                        $values[ $key ] = '';
                        if ( isset( $_POST[ $field ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Checked above.
                                $raw = wp_unslash( $_POST[ $field ] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Sanitized below.
                                if ( 'email' === $key ) {
                                        $values[ $key ] = sanitize_email( $raw );
                                } elseif ( 'note' === $key ) {
                                        $values[ $key ] = sanitize_textarea_field( $raw );
                                } else {
                                        $values[ $key ] = sanitize_text_field( $raw );
                                }
                        }
                }

                $errors = $this->validate_submission( $values );
                if ( ! empty( $errors ) ) {
                        return array(
                                'success' => false,
                                'errors'  => $errors,
                                'values'  => $values,
                                'message' => __( 'Merci de corriger les champs indiqués.', 'club-easy-event' ),
                        );
                }

                $rate_key = 'cee_signup_rate_' . md5( $values['email'] . '|' . $this->get_remote_addr() );
                if ( get_transient( $rate_key ) ) {
                        return array(
                                'success' => false,
                                'errors'  => array(),
                                'values'  => $values,
                                'message' => __( 'Une demande a déjà été envoyée récemment. Merci de patienter avant une nouvelle tentative.', 'club-easy-event' ),
                        );
                }

                $creation = $this->create_player_from_submission( $values );
                if ( is_wp_error( $creation ) ) {
                        return array(
                                'success' => false,
                                'errors'  => array(),
                                'values'  => $values,
                                'message' => $creation->get_error_message(),
                        );
                }

                set_transient( $rate_key, 1, MINUTE_IN_SECONDS );

                $success_message = $this->get_success_message( $values );

                return array(
                        'success' => true,
                        'errors'  => array(),
                        'values'  => $defaults,
                        'message' => $success_message,
                );
        }

        /**
         * Validate submission values.
         *
         * @param array $values Values.
         *
         * @return array
         */
        protected function validate_submission( array $values ) {
                $errors = array();

                if ( '' === $values['first_name'] ) {
                        $errors['first_name'][] = __( 'Le prénom est requis.', 'club-easy-event' );
                }
                if ( '' === $values['last_name'] ) {
                        $errors['last_name'][] = __( 'Le nom est requis.', 'club-easy-event' );
                }
                if ( '' === $values['email'] || ! is_email( $values['email'] ) ) {
                        $errors['email'][] = __( 'Veuillez renseigner une adresse e-mail valide.', 'club-easy-event' );
                }
                $age = (int) $values['age'];
                if ( $age <= 0 ) {
                        $errors['age'][] = __( 'Merci d’indiquer un âge valide.', 'club-easy-event' );
                }

                if ( email_exists( $values['email'] ) ) {
                        $errors['email'][] = __( 'Un compte utilise déjà cette adresse e-mail.', 'club-easy-event' );
                }

                if ( '' !== $values['phone'] ) {
                        $digits = preg_replace( '/\D+/', '', $values['phone'] );
                        if ( strlen( $digits ) < 6 ) {
                                $errors['phone'][] = __( 'Merci d’indiquer un numéro de téléphone valide.', 'club-easy-event' );
                        }
                }

                if ( '' !== $values['emergency_contact_phone'] ) {
                        $digits_emergency = preg_replace( '/\D+/', '', $values['emergency_contact_phone'] );
                        if ( strlen( $digits_emergency ) < 6 ) {
                                $errors['emergency_contact_phone'][] = __( 'Merci d’indiquer un numéro de téléphone d’urgence valide.', 'club-easy-event' );
                        }
                }

                return $errors;
        }
        /**
         * Create user and player post.
         *
         * @param array $values Values.
         *
         * @return array|WP_Error Array with IDs or error.
         */
        protected function create_player_from_submission( array $values ) {
                $login_base = sanitize_user( $values['first_name'] . '.' . $values['last_name'], true );
                if ( ! $login_base ) {
                        $login_base = sanitize_user( $values['email'], true );
                }

                $login = $login_base;
                $attempt = 1;
                while ( username_exists( $login ) ) {
                        $login = $login_base . $attempt;
                        $attempt++;
                }

                $role     = apply_filters( 'cee_player_signup_default_role', 'subscriber', $values );
                $password = wp_generate_password( 12, false );
                $user_id  = wp_insert_user(
                        array(
                                'user_login' => $login,
                                'user_pass'  => $password,
                                'user_email' => $values['email'],
                                'first_name' => $values['first_name'],
                                'last_name'  => $values['last_name'],
                                'role'       => $role,
                        )
                );

                if ( is_wp_error( $user_id ) ) {
                        return $user_id;
                }

                if ( function_exists( 'wp_new_user_notification' ) ) {
                        wp_new_user_notification( $user_id, null, 'both' );
                }

                $player_title = trim( $values['first_name'] . ' ' . $values['last_name'] );
                $player_id    = wp_insert_post(
                        array(
                                'post_type'   => 'cee_player',
                                'post_status' => 'draft',
                                'post_title'  => $player_title,
                        ),
                        true
                );

                if ( is_wp_error( $player_id ) ) {
                        return $player_id;
                }

                $auto_activate = '1' === $this->settings->get_setting( 'signup_auto_activate', '0' );

                $meta = array(
                        '_cee_player_user_id'         => $user_id,
                        '_cee_player_enabled'         => $auto_activate ? 1 : 0,
                        '_cee_player_first_name'      => $values['first_name'],
                        '_cee_player_last_name'       => $values['last_name'],
                        '_cee_player_email'           => $values['email'],
                        '_cee_player_phone'           => $values['phone'],
                        '_cee_player_address_line1'   => $values['address_line1'],
                        '_cee_player_address_line2'   => $values['address_line2'],
                        '_cee_player_city'            => $values['city'],
                        '_cee_player_postal_code'     => $values['postal_code'],
                        '_cee_player_country'         => $values['country'],
                        '_cee_player_emergency_name'  => $values['emergency_contact_name'],
                        '_cee_player_emergency_phone' => $values['emergency_contact_phone'],
                        '_cee_player_age'             => (int) $values['age'],
                        '_cee_player_note'            => $values['note'],
                );

                foreach ( $meta as $key => $value ) {
                        update_post_meta( $player_id, $key, $value );
                }

                $default_team_id = (int) $this->settings->get_setting( 'default_signup_team_id', 0 );
                $default_team_id = apply_filters( 'cee_player_signup_default_team_id', $default_team_id, $player_id, $values );

                if ( $default_team_id > 0 ) {
                        $this->assignment->sync_player_teams( $player_id, array( $default_team_id ) );
                } else {
                        update_post_meta( $player_id, '_cee_player_teams', array() );
                }

                do_action( 'cee_player_signup_created', $player_id, $user_id, $values );

                $this->send_notifications( $player_id, $user_id, $values );

                return array(
                        'user_id'   => $user_id,
                        'player_id' => $player_id,
                );
        }

        /**
         * Send notifications for new signup.
         *
         * @param int   $player_id Player ID.
         * @param int   $user_id   User ID.
         * @param array $values    Submission values.
         *
         * @return void
         */
        protected function send_notifications( $player_id, $user_id, array $values ) {
                $admin_recipients = array( get_option( 'admin_email' ) );
                $admin_recipients = apply_filters( 'cee_player_signup_notification_recipients', $admin_recipients, $player_id, $user_id, $values );

                $subject = sprintf( __( '[%s] Nouvelle inscription joueur', 'club-easy-event' ), wp_specialchars_decode( get_bloginfo( 'name' ) ) );
                $body    = sprintf(
                        'Nom: %1$s %2$s\nEmail: %3$s\nTéléphone: %4$s\nÂge: %5$s',
                        $values['first_name'],
                        $values['last_name'],
                        $values['email'],
                        $values['phone'],
                        $values['age']
                );

                foreach ( array_filter( $admin_recipients ) as $recipient ) {
                        if ( is_email( $recipient ) ) {
                                wp_mail( $recipient, $subject, $body );
                        }
                }

                $send_confirmation = apply_filters( 'cee_player_signup_send_confirmation', true, $player_id, $user_id, $values );
                if ( $send_confirmation && is_email( $values['email'] ) ) {
                        $player_subject = sprintf( __( 'Merci %s, votre inscription est en attente', 'club-easy-event' ), $values['first_name'] );
                        $player_body    = __( 'Nous avons bien reçu votre inscription. Un responsable va vérifier vos informations et activer votre profil.', 'club-easy-event' );
                        wp_mail( $values['email'], $player_subject, $player_body );
                }
        }

        /**
         * Generate success message.
         *
         * @param array $values Submission values.
         *
         * @return string
         */
        protected function get_success_message( array $values ) {
                $message = $this->settings->get_setting( 'signup_success_message', '' );
                if ( ! $message ) {
                        $message = __( 'Merci {first_name}, votre inscription est en attente d’activation par un responsable.', 'club-easy-event' );
                }
                $message = strtr( $message, array( '{first_name}' => $values['first_name'] ) );

                /**
                 * Filter success message for signup.
                 *
                 * @param string $message Message string.
                 * @param array  $values  Submission values.
                 */
                return apply_filters( 'cee_player_signup_success_message', $message, $values );
        }

        /**
         * Retrieve visitor IP address.
         *
         * @return string
         */
        protected function get_remote_addr() {
                $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
                return $ip;
        }
}
