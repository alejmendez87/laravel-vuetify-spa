<template>
  <v-container fluid grid-list-md>
    <v-layout row>
      <v-flex xs12>
        <v-card flat>
          <form @submit.prevent="save" @keydown="form.onKeydown($event)">
            <v-card-title primary-title>
              <h5 class="subheading mb-0">{{ $t('user') }}</h5>
              <v-spacer></v-spacer>
              <v-breadcrumbs divider="/">
                <v-breadcrumbs-item
                  v-for="breadcrumb in breadcrumbs" :key="breadcrumb.text"
                  :disabled="breadcrumb.disabled"
                  :to="breadcrumb.to"
                >
                  {{ breadcrumb.text }}
                </v-breadcrumbs-item>
              </v-breadcrumbs>
            </v-card-title>
            <v-card-text>
              <v-layout row>
                <!-- Name -->
                <v-flex lg3 md4 sm8 xs12>
                <text-input
                  :form="form"
                  :label="$t('name')"
                  :v-errors="errors"
                  :value.sync="form.name"
                  counter="30"
                  name="name"
                  v-validate="'required|max:30'"
                ></text-input>
                </v-flex>

                <!-- Email -->
                <v-flex lg3 md4 sm8 xs12>
                <email-input
                  :form="form"
                  :label="$t('email')"
                  :v-errors="errors"
                  :value.sync="form.email"
                  name="email"
                  v-validate="'required|email'"
                ></email-input>
                </v-flex>
              </v-layout>
            </v-card-text>
            <v-card-actions>
              <submit-button :flat="true" :form="form" :label="$t('save')"></submit-button>
            </v-card-actions>
          </form>
        </v-card>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>

import Form from 'vform'
import axios from 'axios'

export default {
  name: 'users-form-view',
  data() {
    return {
      breadcrumbs: [
        {
          text: this.$t('home'),
          to: {name: 'home'}
        },
        {
          text: this.$t('users'),
          to: {name: 'user'}
        },
        {
          text: this.$t('new')
        }
      ],
      busy: false,
      // post|patch|put|delete|get
      patch: '/api/admin/users/',
      form: new Form({
        id: 0,
        name: '',
        email: ''
      })
    }
  },
  mounted () {
    if (this.$route.params.id) {
      this.form.id = this.$route.params.id
      this.find()
    }
  },
  methods: {
    async save () {
      if (await this.formHasErrors()) return

      this.$emit('busy', true)

      if (this.form.id == 0) {
        const { data } = await this.form.post('/api/users')
      } else {
        const { data } = await this.form.patch('/api/users/' + this.form.id)
      }


      this.$emit('busy', false)
    },
    find () {
      var $this = this
      this.busy = true

      axios.get('/api/users/' + this.form.id)
      .then(response => {
        $this.busy = false
        let data = response.data.data
        this.form.name = data.name
        this.form.email = data.email
      })
    }
  }
}
</script>
