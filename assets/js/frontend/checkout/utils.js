/**
 * Generate markup for a credit card icon based on a passed type.
 *
 * @param {String} type Credit card type.
 * @return HTML markup.
 */
export const getCreditCardIcon = ( type ) => {
  let width;
  let name = type;

  switch ( type ) {
    case 'amex':
      name = 'americanexpress';
      width = 32;
      break;
    default:
      width = 50;
      break;
  }

  return `
    <svg
      width=${ width }
      height=${ 32 }
      class="payment-icon icon-${ name }"
      role="img"
    >
      <use
        href="#icon-${ name }"
        xlink:href="#icon-${ name }">
      </use>
    </svg>`;
};
