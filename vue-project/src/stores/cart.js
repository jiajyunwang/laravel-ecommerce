import { defineStore } from 'pinia'

export const useCartStore = defineStore('cart', {
  state: () => ({
    count: 0,
    selectedIds: [],
    productId: null,
    requestAction: '',
    quantity: 0
  })
})