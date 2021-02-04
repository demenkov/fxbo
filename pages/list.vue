<template>
  <div class="container">
    <div class="content">
      <form name="filter" action="" method="get" @submit.prevent="refresh">
        <label class="form-label" for="from">
          from:
        </label>
        <input type="date" class="form-field" name="from" id="from" v-model="filter.from" @change="refresh"/>
        <label class="form-label" for="to">
          to:
        </label>
        <input type="date" class="form-field" name="to" id="to" v-model="filter.to" @change="refresh"/>
        <label class="form-label" for="provider">
          provider:
        </label>
        <input class="form-field" name="provider" id="provider" v-model="filter.provider" @change="refresh"/>
        <label class="form-label" for="base">
          base:
        </label>
        <input class="form-field" name="base" id="base" v-model="filter.base" @change="refresh"/>
        <label class="form-label" for="quote">
          quote:
        </label>
        <input class="form-field" name="quote" id="quote" v-model="filter.quote" @change="refresh"/>
        <input class="form-button" type="submit" value="Filter"/>
      </form>
      <pre v-text="result"></pre>
      <pre v-text="errors"></pre>
    </div>
    <table class="table">
      <thead>
      <tr>
        <th><a @click.prevent="onHeaderClick('id')">ID</a></th>
        <th><a @click.prevent="onHeaderClick('date')">Date</a></th>
        <th><a @click.prevent="onHeaderClick('provider')">Provider</a></th>
        <th><a @click.prevent="onHeaderClick('base')">Base</a></th>
        <th><a @click.prevent="onHeaderClick('quote')">Quote</a></th>
        <th><a @click.prevent="onHeaderClick('price')">Price</a></th>
        <th><a @click.prevent="onHeaderClick('created')">Created</a></th>
        <th><a @click.prevent="onHeaderClick('updated')">Updated</a></th>
        <th>Delete</th>
      </tr>
      </thead>
      <tbody>
      <template v-for="rate in rates">
        <tr v-bind:key="rate.id">
          <td>{{ rate.id }}</td>
          <td>{{ rate.date }}</td>
          <td>{{ rate.provider }}</td>
          <td>{{ rate.base }}</td>
          <td>{{ rate.quote }}</td>
          <td><input className="form-field" v-model="rate.price" @change="(event) => update(event, rate)" /></td>
          <td>{{ rate.created }}</td>
          <td>{{ rate.updated }}</td>
          <td><a @click.prevent="deleteRate(rate.id)">DELETE</a></td>
        </tr>
      </template>
      </tbody>
    </table>
  </div>
</template>
<script>
import axios from 'axios'

export default {
  data() {
    return {
      rates: {},
      ordering: {
        sort: 'id',
        order: 'desc'
      },
      filter: {
        from: null,
        to: null,
        provider: null,
        base: null,
        quote: null,
      },
    }
  },
  async created () {
    await this.refresh()
  },
  methods: {
    onHeaderClick(sort) {
      if (sort === this.ordering.sort && 'asc' === this.ordering.order) {
        this.ordering.order = 'desc'
      } else {
        this.ordering.order = 'asc'
      }
      this.ordering.sort = sort
      this.refresh()
    },
    deleteRate(id) {
      if (confirm("Are you sure you want to delete rate " + id + "? This cannot be undone.")) {
        axios.delete('http://localhost:8888/rate/' + id).then((response) => {
          this.refresh()
        });
      }
    },
    update(event, rate) {
      if (confirm("Are you sure you want to set price for rate " + rate.id + "?")) {
        axios.put('http://localhost:8888/rate/' + rate.id, {
          price: event.target.value
        }).then((response) => {
          this.refresh()
        });
      }
    },
    refresh() {
      axios.get('http://localhost:8888/rate', {
        params: {...this.ordering, ...this.filter }
      }).then(response => this.rates = response.data.items)
    }
  }
}
</script>
