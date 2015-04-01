<tr class="invoice-item">
    <td class="item-thumb">
        <?php echo $_product->get_image( 'shop_thumbnail', array( 'title' => '' ) ); ?>
    </td>
    <td class="item-name">
        <?php echo $_product->get_title(); ?>
    </td>
    <td class="item-sku">
        <?php echo $_product->get_sku(); ?>
    </td>
    <td class="item-price">
        <?php
        if ( isset( $item['line_total'] ) ) {
            if ( isset( $item['line_subtotal'] ) && $item['line_subtotal'] != $item['line_total'] ) {
                echo '<del>' . wc_price( $order->get_item_subtotal( $item, false, true ), array( 'currency' => $order->get_order_currency() ) ) . '</del> ';
            }
            echo wc_price( $order->get_item_total( $item, false, true ), array( 'currency' => $order->get_order_currency() ) );
        }
        ?>
    </td>
    <td class="item-qty">
        <?php
        echo $item['qty'];

        if ( $refunded_qty = $order->get_qty_refunded_for_item( $item_id ) )
            echo '<small class="refunded">-' . $refunded_qty . '</small>';
        ?>
    </td>

    <?php
    if ( empty( $legacy_order ) && wc_tax_enabled() ) :
        $line_tax_data = isset( $item['line_tax_data'] ) ? $item['line_tax_data'] : '';
        $tax_data      = maybe_unserialize( $line_tax_data );

        foreach ( $order->get_taxes() as $tax_item ) :
            $tax_item_id       = $tax_item['rate_id'];
            $tax_item_total    = isset( $tax_data['total'][ $tax_item_id ] ) ? $tax_data['total'][ $tax_item_id ] : '';
            $tax_item_subtotal = isset( $tax_data['subtotal'][ $tax_item_id ] ) ? $tax_data['subtotal'][ $tax_item_id ] : '';

            ?>
            <td class="item-tax">
                    <?php
                    if ( '' != $tax_item_total ) {
                        if ( isset( $tax_item_subtotal ) && $tax_item_subtotal != $tax_item_total ) {
                            echo '<del>' . wc_price( wc_round_tax_total( $tax_item_subtotal ), array( 'currency' => $order->get_order_currency() ) ) . '</del> ';
                        }

                        echo wc_price( wc_round_tax_total( $tax_item_total ), array( 'currency' => $order->get_order_currency() ) );
                    } else {
                        echo '&ndash;';
                    }

                    if ( $refunded = $order->get_tax_refunded_for_item( $item_id, $tax_item_id ) ) {
                        echo '<small class="refunded">-' . wc_price( $refunded, array( 'currency' => $order->get_order_currency() ) ) . '</small>';
                    }
                    ?>
            </td>
        <?php
        endforeach;
    endif;
    ?>

    <td class="item-total">
        <?php
        if ( isset( $item['line_total'] ) ) {
            if ( isset( $item['line_subtotal'] ) && $item['line_subtotal'] != $item['line_total'] ) {
                echo '<del>' . wc_price( $item['line_subtotal'], array( 'currency' => $order->get_order_currency() ) ) . '</del> ';
            }
            echo wc_price( $item['line_total'], array( 'currency' => $order->get_order_currency() ) );
        }

        if ( $refunded = $order->get_total_refunded_for_item( $item_id ) ) {
            echo '<small class="refunded">-' . wc_price( $refunded, array( 'currency' => $order->get_order_currency() ) ) . '</small>';
        }
        ?>
    </td>
</tr>
