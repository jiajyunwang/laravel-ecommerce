<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { CKEditor } from '@ckeditor/ckeditor5-vue'
import ClassicEditor from '@ckeditor/ckeditor5-build-classic'

const router = useRouter()

const title = ref('')
const price = ref('')
const stock = ref('')
const description = ref('')
const photoFile = ref(null)
const photoPreview = ref(null)
const errors = ref({})
const submitting = ref(false)

function onPhotoChange(e) {
  const file = e.target.files[0]
  if (!file) return
  photoFile.value = file
  photoPreview.value = URL.createObjectURL(file)
}

function getCsrfToken() {
  const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/)
  return match ? decodeURIComponent(match[1]) : ''
}

async function handleSubmit() {
  if (submitting.value) return
  submitting.value = true
  errors.value = {}

  const formData = new FormData()
  formData.append('title', title.value)
  formData.append('price', price.value)
  formData.append('stock', stock.value)
  formData.append('description', description.value)
  if (photoFile.value) formData.append('photo', photoFile.value)

  try {
    const res = await fetch('/api/admin/product/store', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-XSRF-TOKEN': getCsrfToken(),
      },
      body: formData,
    })

    const data = await res.json()
    if (!res.ok) {
      errors.value = data.errors || {}
      return
    }
    router.push('/admin/product')
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="card">
    <div class="card-header">
      <div class="title"><p>新增商品</p></div>
    </div>
    <div class="card-body">
      <form class="form" @submit.prevent="handleSubmit">
        <div class="form-group">
          <label>商品標題<span>*</span></label>
          <input class="form-control" type="text" v-model="title" required>
          <span v-if="errors.title" class="error">{{ errors.title[0] }}</span>
        </div>

        <div class="form-group">
          <label>價格<span>*</span></label>
          <input class="form-control" type="number" min="1" v-model="price" required>
          <span v-if="errors.price" class="error">{{ errors.price[0] }}</span>
        </div>

        <div class="form-group">
          <label>庫存<span>*</span></label>
          <input class="form-control" type="number" min="0" v-model="stock" required>
          <span v-if="errors.stock" class="error">{{ errors.stock[0] }}</span>
        </div>

        <div class="form-group">
          <label>圖檔<span>*</span></label>
          <input class="form-control" type="file" accept="image/*" @change="onPhotoChange" required>
          <img v-if="photoPreview" style="max-width:400px" :src="photoPreview">
          <span v-if="errors.photo" class="error">{{ errors.photo[0] }}</span>
        </div>

        <div class="form-group">
          <label>商品詳情<span>*</span></label>
          <CKEditor :editor="ClassicEditor" v-model="description" />
          <span v-if="errors.description" class="error">{{ errors.description[0] }}</span>
        </div>

        <div class="form-group">
          <button class="create-button" type="submit" :disabled="submitting">刊登</button>
        </div>
      </form>
    </div>
  </div>
</template>
