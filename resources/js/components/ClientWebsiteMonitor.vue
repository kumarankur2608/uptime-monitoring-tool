<script setup>
import { computed, onMounted, ref, watch } from 'vue';

const clients = ref([]);
const websites = ref([]);
const selectedClientId = ref('');
const selectedWebsite = ref(null);
const clientLoadError = ref('');
const websiteLoadError = ref('');
const isLoadingClients = ref(false);
const isLoadingWebsites = ref(false);

const selectedClient = computed(() =>
  clients.value.find((client) => String(client.id) === selectedClientId.value) ?? null,
);

const loadClients = async () => {
  isLoadingClients.value = true;
  clientLoadError.value = '';

  try {
    const response = await fetch('/api/clients', {
      headers: {
        Accept: 'application/json',
      },
    });

    if (!response.ok) {
      throw new Error('Unable to load clients right now.');
    }

    const payload = await response.json();
    clients.value = payload.data ?? [];
  } catch (error) {
    clientLoadError.value = error instanceof Error ? error.message : 'Unable to load clients right now.';
  } finally {
    isLoadingClients.value = false;
  }
};

const loadWebsites = async (clientId) => {
  if (!clientId) {
    websites.value = [];
    websiteLoadError.value = '';
    return;
  }

  isLoadingWebsites.value = true;
  websiteLoadError.value = '';

  try {
    const response = await fetch(`/api/clients/${clientId}/websites`, {
      headers: {
        Accept: 'application/json',
      },
    });

    if (!response.ok) {
      throw new Error('Unable to load websites for the selected client.');
    }

    const payload = await response.json();
    websites.value = payload.data ?? [];
  } catch (error) {
    websites.value = [];
    websiteLoadError.value = error instanceof Error
      ? error.message
      : 'Unable to load websites for the selected client.';
  } finally {
    isLoadingWebsites.value = false;
  }
};

const openDialog = (website) => {
  selectedWebsite.value = website;
};

const closeDialog = () => {
  selectedWebsite.value = null;
};

const continueToWebsite = () => {
  if (selectedWebsite.value) {
    window.open(selectedWebsite.value.visit_url, '_blank', 'noopener,noreferrer');
  }

  closeDialog();
};

watch(selectedClientId, loadWebsites);

onMounted(loadClients);
</script>

<template>
  <main class="shell">
    <section class="hero">
      <div class="eyebrow">Monitoring dashboard</div>
      <h1>Uptime signals for every client.</h1>
      <p>
        Select a client email to review the homepages currently being monitored.
        Website checks run every fifteen minutes, and alerts are queued as soon as a homepage
        becomes unreachable or starts returning an error response.
      </p>

      <div class="panel-grid">
        <section class="panel">
          <h2>Client websites</h2>
          <p>Pick a client to see the websites associated with that account.</p>

          <div class="field">
            <label for="client-email">Client email</label>
            <select
              id="client-email"
              v-model="selectedClientId"
              :disabled="isLoadingClients"
            >
              <option value="">Select a client</option>
              <option
                v-for="client in clients"
                :key="client.id"
                :value="String(client.id)"
              >
                {{ client.email }}
              </option>
            </select>
          </div>

          <p
            v-if="clientLoadError"
            class="status-text error"
          >
            {{ clientLoadError }}
          </p>
          <p
            v-else-if="isLoadingClients"
            class="status-text"
          >
            Loading clients...
          </p>
          <p
            v-else-if="selectedClient"
            class="helper-text"
          >
            {{ selectedClient.websites_count }} website(s) currently assigned to {{ selectedClient.email }}.
          </p>

          <p
            v-if="websiteLoadError"
            class="status-text error"
          >
            {{ websiteLoadError }}
          </p>
          <p
            v-else-if="selectedClientId && isLoadingWebsites"
            class="status-text"
          >
            Loading websites...
          </p>
          <p
            v-else-if="selectedClientId && websites.length === 0"
            class="helper-text"
          >
            No websites have been configured for this client yet.
          </p>

          <ul
            v-if="websites.length > 0"
            class="website-list"
          >
            <li
              v-for="website in websites"
              :key="website.id"
            >
              <a
                :href="website.visit_url"
                class="website-link"
                @click.prevent="openDialog(website)"
              >
                {{ website.url }}
              </a>
            </li>
          </ul>
        </section>

        <aside class="panel meta-card">
          <div class="meta-item">
            <p class="meta-label">Check cadence</p>
            <p class="meta-value">Every 15 minutes</p>
          </div>
          <div class="meta-item">
            <p class="meta-label">Alert condition</p>
            <p class="meta-value">10 second timeout or HTTP error</p>
          </div>
          <div class="meta-item">
            <p class="meta-label">Delivery</p>
            <p class="meta-value">Queued email from do-not-reply@example.com</p>
          </div>
        </aside>
      </div>
    </section>

    <div
      v-if="selectedWebsite"
      class="dialog-backdrop"
      role="presentation"
      @click.self="closeDialog"
    >
      <div
        class="dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="visit-title"
      >
        <h3 id="visit-title">Open monitored website</h3>
        <p>
          You are about to visit {{ selectedWebsite.url }}. Do you want to continue?
        </p>

        <div class="dialog-actions">
          <button
            type="button"
            class="button secondary"
            @click="closeDialog"
          >
            Cancel
          </button>
          <button
            type="button"
            class="button primary"
            @click="continueToWebsite"
          >
            Continue
          </button>
        </div>
      </div>
    </div>
  </main>
</template>
