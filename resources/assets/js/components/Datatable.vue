<template>
  <v-container fluid grid-list-md>
    <v-layout row>
      <v-flex xs12>
        <v-card>
          <v-card-title>
            {{ $t('users') }}
            <v-spacer></v-spacer>
            <v-text-field
              append-icon="search"
              :label="$t('search')"
              single-line
              hide-details
              v-model="search"
            ></v-text-field>
          </v-card-title>
          <v-data-table
            v-bind:headers="headers"
            v-bind:items="items"
            v-bind:search="search"
            v-bind:pagination.sync="pagination"
            :total-items="totalItems"
            :loading="loading">
            <template slot="items" slot-scope="props">
              <tr @click="click(props.item.id)">
                <td>{{ props.item.name }}</td>
                <td>{{ props.item.email }}</td>
              </tr>
            </template>
          </v-data-table>
        </v-card>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
import axios from 'axios'
export default {
  name: 'v-datatable',
  data() {
    return {
      timer: null,
      search: '',
      totalItems: 0,
      items: [],
      loading: true,
      pagination: {
        rowsPerPage: 10,
        search: ''
      },
      headers: [
        { text: this.$t('name'), value: 'name', align: 'left' },
        { text: this.$t('email'), value: 'email', align: 'left' }
      ]
    }
  },
  watch: {
    pagination: {
      handler () {
        this.getDataFromApi()
          .then(data => {
            this.items = data.items
            this.totalItems = data.total
          })
      },
      deep: true
    }
  },
  mounted () {
    this.getDataFromApi()
      .then(data => {
        this.items = data.items
        this.totalItems = data.total
      })
  },
  methods: {
    click (id) {
      alert(id)
    },
    getDataFromApi () {
      var $this = this
      this.loading = true
      return new Promise((resolve, reject) => {
        const { sortBy, descending, page, rowsPerPage } = this.pagination
        const search = this.search
        const columns = []

        for (let i in this.headers) {
          columns.push({
            data : this.headers[i].value,
            name : this.headers[i].value,
            searchable : true,
            orderable : true,
            search : {
              value : '',
              regex : false
            }
          })
        }

        clearTimeout(this.timer)
        this.timer = setTimeout(() => {
          axios.get('/api/users', {
            params: {
              columns : JSON.stringify(columns),
              sort : {
                sortBy : sortBy,
                descending: descending,
                page : page,
                rowsPerPage : rowsPerPage,
              },
              search : {
                value : search
              }
            }
          })
          .then(response => {
            if (response.status == 200) {
              let items = response.data.data
              const total = response.data.recordsTotal

              console.log(response.data)
              $this.loading = false

              resolve({
                items,
                total
              });
            }
          })
          .catch(error => {
            if (error.response) {
              console.log(error.response)
            }
          })
        },1000)
      })
    }
  }
}
</script>
