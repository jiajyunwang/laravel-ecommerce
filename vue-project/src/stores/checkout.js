import { defineStore } from 'pinia'

export const useCheckoutStore = defineStore('checkout', {
  state: () => ({
    productId: null,
    requestAction: 'checkout',
    quantity: 0
  })
})