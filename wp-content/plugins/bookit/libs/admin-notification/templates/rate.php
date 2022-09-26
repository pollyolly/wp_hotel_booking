<div class="stm-plugin-admin-notice <?php echo esc_attr( $plugin_name ); ?>">
    <span class="stm-plugin-admin-notice-close" data-type="later">×</span>
    <?php if ( isset( $logo ) ): ?>
        <div class="stm-plugin-admin-notice-logo">
            <img src="<?php echo esc_attr( $logo ); ?>" alt="">
        </div>
    <?php endif; ?>
    <div class="stm-plugin-admin-notice__content">
        <h3><?php echo ! empty( $title ) ? wp_kses( $title, [] ) : ''; ?></h3>
        <p><?php echo ! empty( $content ) ? wp_kses( $content, [ 'br' => array(), 'strong' => array(), 'span' => array() ] ) : ''; ?></p>
        <div class="stm-plugin-admin-notice-stars">
            <ul>
                <li class="star"><i class="notice-star"></i></li>
                <li class="star"><i class="notice-star"></i></li>
                <li class="star"><i class="notice-star"></i></li>
                <li class="star"><i class="notice-star"></i></li>
                <li class="star"><i class="notice-star"></i></li>
            </ul>
        </div>
        <div class="plugin-notice">
            <input type="hidden" name="plugin-name" value="<?php echo esc_attr( $plugin_name ); ?>">
            <input type="hidden" name="plugin-event" value="<?php echo isset( $event ) ? esc_attr( $event ) : ''; ?>">
            <a <?php if ( isset( $submit_link ) ): ?> href="<?php echo esc_attr( $submit_link ); ?>" <?php endif; ?>
                    data-type="sure" target="_blank" class="plugin-notice-btn active">Sure!</a>
            <button data-type="later" class="plugin-notice-btn">Maybe Later</button>
            <button data-type="decline" class="plugin-notice-btn">I already did!</button>
        </div>
    </div>
</div>