<template>
  <div class="container">
    <div class="content">
      <form name="exchange" action="" method="post" @submit.prevent="onSubmit">
        <label class="form-label" for="amount">
          amount:
        </label>
        <input class="form-field" name="amount" id="amount" required v-model="amount" @change="onSubmit"/>
        <label class="form-label" for="from">
          from:
        </label>
        <input class="form-field" name="from" id="from" maxlength="3" required v-model="from" @change="onSubmit"/>
        <label class="form-label" for="to">
          to:
        </label>
        <input class="form-field" name="to" id="to" maxlength="3" required v-model="to" @change="onSubmit"/>
        <input class="form-button" type="submit" value="Calculate"/>
      </form>
      <pre v-text="result"></pre>
      <pre v-text="errors"></pre>
    </div>
  </div>
</template>
<script>
export default {
  data() {
    return {
      from: '',
      to: '',
      amount: '',
      result: '',
      errors: ''
    }
  },
  methods: {
    onSubmit() {
      if (this.from === '') {
        return
      }
      if (this.to === '') {
        return
      }
      if (this.amount === '') {
        return
      }
      this.$axios.get('http://localhost:8888/exchange', {
        params: {
          from: this.from,
          to: this.to,
          amount: this.amount,
        }
      }).then(response => {
        this.result = this.amount + ' ' + this.from + ' = ' + response.data + ' ' + this.to
        this.errors = ''
      }).catch(error => {
        this.errors = error.response.data.message
        this.result = ''
      })
    }
  }
}
</script>
