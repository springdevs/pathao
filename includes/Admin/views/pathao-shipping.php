<div class="sdevs_sidebar_form">
	<input type="hidden" value="<?php echo wp_create_nonce('pathao_send_order'); ?>" id="pathao_send_order_nonce">
	<input type="hidden" value="<?php echo $order_id; ?>" id="pathao_order_id">
	<p class="form-field">
		<label for="pathao_store"><b>Store</b></label>
		<select style="width: 100%;" id="pathao_store" name="pathao_store">
			<option value="">Select Store</option>
			<?php
			/** @var Array $stores */
			foreach ($stores as $store) :
			?>
				<option value="<?php echo $store->store_id; ?>"><?php _e($store->store_name, 'sdevs_pathao'); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p class="form-field">
		<label for="pathao_delivery_type"><b>Delivery Type</b></label>
		<select style="width: 100%;" name="pathao_delivery_type" id="pathao_delivery_type">
			<option value="48">Normal Delivery</option>
			<option value="12">On-demand Delivery</option>
		</select>
	</p>
	<p class="form-field">
		<label for="pathao_item_type"><b>Item Type</b></label>
		<select style="width: 100%;" name="pathao_item_type" id="pathao_item_type">
			<option value="1">Document</option>
			<option value="2">Parcel</option>
		</select>
	</p>
	<p class="form-field">
		<label for="pathao_city"><b>City</b></label>
		<select style="width: 100%;" id="pathao_city" name="pathao_city">
			<option value="">Select City</option>
			<?php
			/** @var Array $stores */
			foreach ($cities as $city) :
			?>
				<option value="<?php echo $city->city_id; ?>"><?php _e($city->city_name, 'sdevs_pathao'); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p class="form-field" id="pathao_zone_select" style="display: none;">
		<label for="pathao_zone"><b>Zone</b></label>
		<select style="width: 100%;" id="pathao_zone" name="pathao_zone">
		</select>
	</p>
	<p class="form-field" id="pathao_area_select" style="display: none;">
		<label for="pathao_area"><b>Area</b></label>
		<select style="width: 100%;" id="pathao_area" name="pathao_area">
		</select>
	</p>
	<p class="form-field">
		<label for="pathao_weight"><b>Total weight (kg)</b></label>
		<input type="text" value="<?php echo esc_html($total_weight); ?>" id="pathao_weight" name="pathao_weight" />
	</p>
	<p class="form-field">
		<label for="pathao_amount"><b>Amount to Collect</b></label>
		<input type="text" value="<?php echo esc_html($amount); ?>" id="pathao_amount" name="pathao_amount" />
	</p>
	<p class="form-field">
		<label for="pathao_special_instruction"><b>Special Instruction</b></label>
		<textarea style="width: 100%;" id="pathao_special_instruction" name="pathao_special_instruction"></textarea>
	</p>

	<input class="button-primary" id="pathao_submit_shipping" type="button" value="Send Order" />
	<div class="spinner" style="float:none;width:auto;height:auto;padding:10px 0 10px 50px;background-position:20px 0;position: relative;left: -25px;top: -1px;"></div>
</div>