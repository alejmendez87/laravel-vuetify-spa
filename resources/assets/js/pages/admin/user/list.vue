<template>
  <v-container fluid grid-list-md>
    <v-layout row>
      <v-flex xs12>
        <v-breadcrumbs divider="/">
          <v-breadcrumbs-item
            v-for="breadcrumb in breadcrumbs" :key="breadcrumb.text"
            :disabled="breadcrumb.disabled"
            :to="breadcrumb.to"
          >
            {{ breadcrumb.text }}
          </v-breadcrumbs-item>
        </v-breadcrumbs>
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
          <v-card-text>
            <v-data-table
              v-bind:headers="headers"
              v-bind:items="items"
              v-bind:search="search"
              v-bind:pagination.sync="pagination"
              :total-items="totalItems"
              :loading="loading">
              <template slot="items" slot-scope="props">
                <tr>
                  <td style="width: 45%">{{ props.item.name }}</td>
                  <td style="width: 45%">{{ props.item.email }}</td>
                  <td class="text-xs-center" style="width: 10%">
                    <v-menu offset-x left bottom>
                        <v-btn
                            icon
                            slot="activator">
                          <v-icon>more_vert</v-icon>
                        </v-btn>
                        <v-list>
                          <v-list-tile @click="edit(props.item.id)">
                            <v-list-tile-title>{{ $t('edit') }}</v-list-tile-title>
                          </v-list-tile>
                          <v-list-tile @click="del(props.item.id)">
                            <v-list-tile-title>{{ $t('delete') }}</v-list-tile-title>
                          </v-list-tile>
                        </v-list>
                      </v-menu>
                  </td>
                </tr>
              </template>
            </v-data-table>
          </v-card-text>
          <v-card-actions>
            <v-btn
              color="primary"
              rigth
              large
              :to="{name: 'user-new'}"
            >
              <v-icon>add</v-icon> {{ $t('new') }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
import axios from 'axios'
export default {
  name: 'users-list-view',
  data() {
    return {
      breadcrumbs: [
        {
          text: this.$t('home'),
          to: {name: 'home'}
        },
        {
          text: this.$t('users')
        }
      ],
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
        { text: this.$t('email'), value: 'email', align: 'left' },
        { text: this.$t('actions'), value: 'actions', sortable: false, align: 'center' }
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
    edit (id) {
      this.$router.push({ name: 'user-edit', params: { id: id }})
    },
    del (id) {
      this.$router.push({ name: 'user-edit', params: { id: id }})
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
