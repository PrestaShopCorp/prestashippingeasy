<?php


class prestashippingeasycallbackModuleFrontController extends ModuleFrontController
{
	private $shippingeasy;

	public function initContent()
	{
		parent::initContent();
		$this->shippingeasy = new PrestaShippingEasy();

		// Load library now
		require(dirname(__FILE__) . '/../../library/ShippingEasy.php');

		// Errors
		require(dirname(__FILE__) . '/../../library/Error.php');
		require(dirname(__FILE__) . '/../../library/ApiError.php');
		require(dirname(__FILE__) . '/../../library/ApiConnectionError.php');
		require(dirname(__FILE__) . '/../../library/AuthenticationError.php');
		require(dirname(__FILE__) . '/../../library/InvalidRequestError.php');

		require(dirname(__FILE__) . '/../../library/ApiRequestor.php');
		require(dirname(__FILE__) . '/../../library/Authenticator.php');
		require(dirname(__FILE__) . '/../../library/Object.php');
		require(dirname(__FILE__) . '/../../library/Order.php');
		require(dirname(__FILE__) . '/../../library/Signature.php');
		require(dirname(__FILE__) . '/../../library/SignedUrl.php');
		require(dirname(__FILE__) . '/../../library/Cancellation.php');

		$values = Tools::file_get_contents('php://input');
		$output = Tools::jsonDecode($values, true);

      	$order_id = $output['shipment']['orders'][0]['external_order_identifier'];
        $tracking_number = $output['shipment']['tracking_number'];
        $carrier_key = $output['shipment']['carrier_key'];
        $carrier_service_key = $output['shipment']['carrier_service_key'];
        $shipment_cost_cents = $output['shipment']['shipment_cost'];
        $shipment_cost = ($shipment_cost_cents / 100);

        $order=new Order((int)$order_id);
        if (Validate::isLoadedObject($order)) {
        	$order->setCurrentState(Configuration::get('PS_OS_SHIPPING'));
	        $comment_update = 'Shipping Tracking Number: ' . $tracking_number . ' Carrier Key: ' . $carrier_key . ' Carrier Service Key: ' . $carrier_service_key . ' Cost: ' . $shipment_cost;

			$msg = new Message();
			$msg->message = Tools::substr($comment_update,0,1600);
			$msg->id_order = (int)($order_id);
			$msg->private = 1;
			$msg->add();

        	if (Validate::isTrackingNumber($tracking_number)) {
				$order->shipping_number = $tracking_number;
				$order->update();

				$order_carrier = new OrderCarrier($order->getIdOrderCarrier());
				if (Validate::isLoadedObject($order_carrier)) {
					$order_carrier->tracking_number = $tracking_number;
					$order_carrier->update();
				}
			}
		}

 	die();
	}
}