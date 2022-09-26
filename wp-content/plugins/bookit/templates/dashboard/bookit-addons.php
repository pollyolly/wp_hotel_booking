<div class="bookit_addons">
    <div class="container">
        <div class="addon-header">
            <div class="bookit-icon"></div>
            <div class="title"><?php echo $translations['addons_title'];?></div>
            <div class="description"><?php echo $translations['addons_description'];?></div>
        </div>
        <div class="addon-content">
            <div class="pricing">
                <div class="left annual active"><?php esc_html_e( 'Annual', 'bookit' ); ?></div>
                <div class="switch">
                    <input type="checkbox" id="addonPriceType">
                    <label for="addonPriceType"></label>
                </div>
                <div class="right lifetime"><?php esc_html_e( 'Lifetime', 'bookit' ); ?></div>
            </div>
            <div class="addon-list">
                
                <?php foreach ( $freemius_info as $addon => $data ) : ?>
                    <div class="addon <?php echo $addon;?>">
	                    <?php
                        $activePlan = current(array_filter($data['plan'], function($p) { return $p->active==true; }));
	                    $activePlan = $activePlan ? clone($activePlan) : $activePlan;
	                    ?>
                        <?php if ( $addon == 'bookit-all-addons' ):?>
                        <span class="popular">
                            <i class="popular-icon"></i>
                            <?php echo $translations['popular'];?>
                        </span>
                        <?php endif;?>

                        <div class="icons">
                            <i class="icon"></i>
                        </div>
                        <h2 class="title"><?php echo $data['title'];?></h2>
                        <div class="price <?php echo $addon;?>" >

                            <?php if ( $activePlan ):?>
	                            <?php
                                    $existPlans = ['lifetime' => [], 'annual' => []];
                                    $price = ($activePlan->is_lifetime) ? $activePlan->lifetime_price : $activePlan->annual_price;
	                                if ( $activePlan->is_lifetime == false ):
			                            $existPlans['lifetime'] = $data['plan'];
			                            $existPlans['annual']   = array_map( function ($plan) use ($activePlan) {
				                            if ( (int)$plan->licenses >= (int)$activePlan->licenses ) {
					                            return $plan;
				                            }else{
				                                return;
                                            }
			                            }, $data['plan']);
		                            endif;

		                            if ( $activePlan->is_lifetime == true ):
			                            $existPlans['annual']   = [];
			                            $existPlans['lifetime'] = array_map( function ($plan) use ($activePlan ) {
				                            if ( (int)$plan->licenses >= $activePlan->licenses ) {
					                            return $plan;
				                            }else{
				                                return;
                                            }
			                            }, $data['plan']);
		                            endif;
	                            ?>
	                            <?php foreach ( $data['plan'] as $plan ) : ?>
                                    <p class="<?php echo $plan->licenses;?> <?php echo ($plan->licenses == $activePlan->licenses && $plan->is_lifetime == $activePlan->is_lifetime) ? 'active active-plan current-plan':'hidden'; ?>"
                                       data-lifetime="<?php echo $activePlan->is_lifetime;?>"
                                       data-licenses="<?php echo $activePlan->licenses;?>"
                                       data-price="<?php echo $activePlan->is_lifetime ? $activePlan->lifetime_price: $activePlan->annual_price;?>"
                                       data-license-text="<?php echo $activePlan->data['text'];?>">
                                        <span class="plan-price"
                                              data-annual="<?php echo $plan->annual_price;?>"
                                              data-lifetime="<?php echo $plan->lifetime_price;?>"
                                              data-url="<?php echo $plan->url;?>">
                                            $<?php echo $price;?>
                                        </span>
                                        <?php if (!$activePlan->is_lifetime):?>
                                        <span class="plan-period">
                                            <?php echo __('/per year');?>
                                        </span>
                                        <?php endif;?>
                                    </p>
	                            <?php endforeach;?>
                            <?php endif;?>

	                        <?php if ( isset( $data['plan'] ) && !$activePlan ): ?>
                                <?php foreach ( $data['plan'] as $plan ) : ?>
                                    <p class="<?php echo $plan->licenses;?> <?php echo ($plan->licenses == 1) ? 'active':'hidden'; ?>">
                                        <span class="plan-price"
                                              data-annual="<?php echo $plan->annual_price;?>"
                                              data-lifetime="<?php echo $plan->lifetime_price;?>"
                                              data-url="<?php echo $plan->url;?>">
                                            $<?php echo $plan->annual_price;?>
                                        </span>
                                        <span class="plan-period">
                                            <?php echo __('/per year');?>
                                        </span>
                                    </p>
                                <?php endforeach;?>
	                        <?php endif;?>
                        </div>
                        <p class="info">
                            <?php echo $descriptions[$addon]; ?>
                        </p>
	                    <?php if ( !empty( $data['latest'] ) ) : ?>
                            <div class="developer-info">
                                <span class="version-label">
                                    <?php echo $translations['version']; ?>
                                </span>
                                <span>
                                    <?php echo esc_html($data['latest']['version']); ?>
                                    <a href="https://docs.stylemixthemes.com/bookit-calendar/changelog/" target="_blank">
                                        <?php echo $translations['view_changelog']; ?>
                                    </a>
                                </span>
                            </div>
                        <?php endif;?>

	                    <?php $firstPlan = reset($data['plan']); ?>
                        <?php if ( $activePlan && $activePlan->licenses == 25 && $activePlan->is_lifetime): ?>
                            <div class="exist-license">
                                <span><?php echo sprintf( $translations['active_license'], $activePlan->data['text'] );?></span>
                            </div>
                        <?php elseif ( $activePlan ):?>
                            <div class="exist-license">
                                <span><?php echo sprintf( $translations['license_purchased'], $activePlan->data['text'], ( $activePlan->is_lifetime ) ? $translations['lifetime'] : '' );?></span>
                            </div>
                        <?php endif;?>

	                    <?php
	                    $customSelectCls = $addon;
                        if ( $activePlan && $activePlan->licenses == 25 && $activePlan->is_lifetime): ?>
                            <div class="action">
                                <button class="active-addon"><?php echo $translations['active'];?></button>
                            </div>
                        <?php elseif( $activePlan ):?>

                            <div class="action">
                                <?php
                                if ( $activePlan->is_lifetime || ( !$activePlan->is_lifetime && $activePlan->licenses == 25 ) ):
	                                $customSelectCls .= ' active-annual hidden';
                                ?>
                                    <button class="active-addon active-annual-btn"><?php echo $translations['active'];?></button>
	                            <?php endif;?>

                                <div class="custom-select <?php echo $customSelectCls;?>">
                                    <div class="value" data-value="<?php echo $activePlan->licenses;?>">
                                        <?php echo esc_html( $activePlan->data['text'] ); ?>
                                    </div>
                                    <div class="custom-options">
                                        <?php foreach ( $existPlans['annual'] as $plan ) : ?>
                                            <?php if ( $plan !== null ):?>
                                            <span class="annual custom-option <?php echo ( $activePlan && $activePlan->id == $plan->id && !$activePlan->is_lifetime) ? 'disable':'';?>" data-value="<?php echo $plan->licenses;?>">
                                                <?php echo esc_html($plan->data['text']); ?>
                                            </span>
                                            <?php endif;?>
                                        <?php endforeach; ?>

	                                    <?php foreach ( $existPlans['lifetime'] as $plan ) : ?>
		                                    <?php if ( $plan !== null ):?>
                                                <span class="lifetime hidden custom-option <?php echo ( $activePlan && $activePlan->id == $plan->id && $activePlan->is_lifetime) ? 'disable':'';?>" data-value="<?php echo $plan->licenses;?>">
                                                <?php echo esc_html($plan->data['text']); ?>
                                                </span>
		                                    <?php endif;?>
	                                    <?php endforeach; ?>
                                    </div>
                                </div>
                                <a data-license-url="<?php echo $activePlan->url;?>" target="_blank" class="buy <?php echo $addon.' '. $customSelectCls;?>" href="<?php echo esc_url($activePlan->url);?>">
                                    <?php echo  $activePlan == false ? $translations['buy']: $translations['upgrade']; ?>
                                </a>
                            </div>
                        <?php else:?>
                            <div class="action">
                                <div class="custom-select">
                                    <div class="value" data-value="<?php echo $firstPlan->licenses;?>">
					                    <?php echo esc_html( $firstPlan->data['text'] ); ?>
                                    </div>
                                    <div class="custom-options">
					                    <?php foreach ( $data['plan'] as $plan ) : ?>
                                            <span class="custom-option <?php echo ( $activePlan && $activePlan->id == $plan->id ) ? 'disable':'';?>" data-value="<?php echo $plan->licenses;?>">
                                            <?php echo esc_html($plan->data['text']); ?>
                                            </span>
					                    <?php endforeach; ?>
                                    </div>
                                </div>
                                <a target="_blank" class="buy <?php echo $customSelectCls;?>" href="<?php echo esc_url($firstPlan->url);?>">
				                    <?php echo  $activePlan == false ? $translations['buy']: $translations['upgrade']; ?>
                                </a>
                            </div>
                        <?php endif;?>

                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="addon-footer">
            <p class="terms_content">
		        <?php
		        $url       = 'https://stylemixthemes.com/subscription-policy/';
		        $span_attr = 'class="stm_terms_content_support" data-support-lifetime="' . esc_attr__( 'Lifetime', 'bookit' ) . '" data-support-annual="' . esc_attr__( '1 year', 'bookit' ) . '"';
		        printf( __( 'You get <a href="%1$s"><span %2$s>1 year</span> updates and support</a> from the date of purchase. We offer 30 days Money Back Guarantee based on <a href="%1$s">Refund Policy</a>.', 'bookit' ), $url, $span_attr );
		        ?>
            </p>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function ($) {
    function replaceUrlParam( url, paramName, paramValue ) {
        if (paramValue == null) {
            paramValue = '';
        }
        var pattern = new RegExp('\\b('+paramName+'=).*?(&|#|$)');
        if (url.search(pattern)>=0) {
            return url.replace(pattern,'$1' + paramValue + '$2');
        }
        url = url.replace(/[?#]$/,'');
        return url + (url.indexOf('?')>0 ? '&' : '?') + paramName + '=' + paramValue;
    }

    function hasParentClass( child, classList ) {
        for (var i = 0; i < classList.length; i ++ ) {
            if(child.className.split(' ').indexOf(classList[i]) >= 0) return true;
        }
        //Throws TypeError if no parent
        try{
            return child.parentNode && hasParentClass(child.parentNode, classList);
        }catch(TypeError){
            return false;
        }
    }

    const onClickOutside = (e) => {
        if (e.target.className.includes('custom-select') || e.target.className.includes('custom-option') || hasParentClass(e.target, ['action'])) {
            return;
        }
        $('.custom-select').each(function () {
            $(this).removeClass('open');
            $(this).children('.custom-options').removeClass('open');
        })
        window.removeEventListener("click", onClickOutside);
    };

    $('#addonPriceType').attr('checked', false);

    $('.custom-select').on('click', function( event ) {

        var activePlan = $(this).parents('.addon').find('.price > .active-plan');
        var priceType  = ($('#addonPriceType').is(':checked')) ? 'lifetime': 'annual';
        /** Not show price drop down for lower licencses than exist **/
        if ( activePlan.length > 0 &&  $.trim( $(this).find('.' + priceType).html() ) == "" ) {
            return;
        }

        window.addEventListener("click", onClickOutside);
        let selectClass = $(this).attr('class');
        if ( selectClass.includes('open') ) {
            $(this).removeClass('open');
            $(this).children('.custom-options').removeClass('open');
        }else{
            $(this).addClass('open');
            $(this).children('.custom-options').addClass('open');
        }
    });

    $('.custom-option').on('click', function( event ) {
        if ($(this).hasClass('disable')) {
            return;
        }
        var license   = $(this).attr('data-value');
        var title     = $(this).text();
        var priceType = ($('#addonPriceType').is(':checked')) ? 'lifetime': 'annual';

        let valueEl =  $(this).parents('.custom-select').find('.value');
        valueEl.text(title);
        valueEl.attr('data-value', license);

        var parent = $(this).parents('.addon');
        parent.find('.price > p').each(function () {
            if ( $(this).hasClass(license) ) {
                var price        = $(this).children('.plan-price');
                var priceForType = price.data(priceType);
                var url          = replaceUrlParam(price.data('url'), 'period', priceType);
                /** set new price */
                price.text('$' + priceForType);

                var activeLicense  = $(this).attr('data-licenses');
                var activeLifetime = $(this).attr('data-lifetime') == 1 ? true: false;
                if ( !activeLifetime && priceType == 'lifetime' && license == activeLicense ) {
                    $(this).removeClass('active-plan');
                }else if ( license == activeLicense ){
                    $(this).addClass('active-plan');
                }

                /** hide 'per year' if lifetime */
                if ( priceType == 'lifetime') {
                    $(this).children('.plan-period').addClass('hidden');
                }else{
                    $(this).children('.plan-period').removeClass('hidden');
                }

                /** set new url */
                $(this).parents('.addon').children('.action').find('a[class*="buy"]').attr('href', url);

                $(this).addClass('active');
                $(this).removeClass('hidden');

            }else{
                $(this).addClass('hidden');
                $(this).removeClass('active');
            }
        })
    });

    $('#addonPriceType').on('change', function () {

        var priceType = 'annual';
        if (this.checked) priceType = 'lifetime';

        let parent      = $(this).closest('.pricing');
        let annual      = parent.find('.annual');
        let lifetime    = parent.find('.lifetime');
        annual.toggleClass('active', !this.checked);
        lifetime.toggleClass('active', this.checked);

        $('.addon').each(function () {
            var addonObj     = $(this);
            var price        = addonObj.find('.price > .active .plan-price');
            var priceForType = price.data(priceType);
            var url          = replaceUrlParam(price.data('url'), 'period', priceType);

            /** set new price */
            price.text('$' + priceForType);

            /** hide 'per year' if lifetime */
            if ( priceType == 'lifetime') {
                addonObj.find('.price > .active .plan-period').addClass('hidden');
                addonObj.find('.active-annual').each(function () {
                    $(this).removeClass('hidden');
                });
                addonObj.find('.active-annual-btn').addClass('hidden');
            }else{
                addonObj.find('.price > .active .plan-period').removeClass('hidden');
                addonObj.find('.active-annual').each(function () {
                    $(this).addClass('hidden');
                });
                addonObj.find('.active-annual-btn').removeClass('hidden');
            }

            /** set new url */
            var action     = addonObj.children('.action');
            var activePlan = addonObj.find('.price > .current-plan');

            if ( activePlan.length > 0 ) {
                var activeLicense  = activePlan.attr('data-licenses');
                var activeLifetime = activePlan.attr('data-lifetime') == 1 ? true: false;

                /** self url if lower url params than exist license **/
                var choosenLicense = action.find('.value').attr('data-value');
                if ( !activeLifetime && priceType == 'lifetime' && choosenLicense == activeLicense) {
                    activePlan.removeClass('active-plan');
                }else if (choosenLicense == activeLicense){
                    activePlan.addClass('active-plan');
                }

                if ( ( activeLifetime && priceType == 'annual' ) || ( !activeLifetime && priceType == 'annual' && parseInt( choosenLicense ) < parseInt ( activeLicense) ) ) {
                    var url = action.find('a[class*="buy"]').attr('data-license-url');
                    action.find('.value').text(activePlan.attr('data-license-text'));
                    action.find('.value').attr('data-value', activeLicense);
                    /** set new price */
                    price.text('$' + activePlan.attr('data-price'));
                }

                action.find('.custom-option').each(function () {
                    if ( $(this).attr('class').split(' ').includes(priceType) ) {
                        $(this).addClass('show');
                        $(this).removeClass('hidden');
                    }else{
                        $(this).addClass('hidden');
                        $(this).removeClass('show');
                    }
                });
            }


            action.find('a[class*="buy"]').attr('href', url);

        })
    });
});
</script>
