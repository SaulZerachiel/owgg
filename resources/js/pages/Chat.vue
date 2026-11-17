<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

type Message = {
    role: 'user' | 'assistant';
    content: string;
};

// L'historique local sert à conserver le contexte de la discussion affichée.
const messages = ref<Message[]>([]);
const message = ref('');
const loading = ref(false);
const error = ref('');

const props = defineProps<{
    remaining: number;
}>();
const remaining = ref(props.remaining);

async function sendMessage() {
    const content = message.value.trim();

    if (!content || loading.value || remaining.value === 0) {
        // On évite les doubles envois et les appels lorsque le quota est épuisé.
        return;
    }

    error.value = '';
    loading.value = true;
    message.value = '';
    messages.value.push({ role: 'user', content });

    try {
        // Le token CSRF est lu dans la page Blade puis renvoyé avec la requête POST.
        const csrf = document
            .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.getAttribute('content');
        const response = await fetch('/chat/message', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                message: content,
                // Le dernier message est envoyé séparément comme nouveau message utilisateur.
                history: messages.value.slice(0, -1),
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(
                data.message ??
                    data.errors?.message?.[0] ??
                    'Une erreur est survenue.',
            );
        }

        messages.value.push({ role: 'assistant', content: data.answer });
        remaining.value = data.remaining;
    } catch (exception) {
        // Si l'appel échoue, on retire le message optimiste de l'interface.
        messages.value.pop();
        error.value =
            exception instanceof Error
                ? exception.message
                : 'Le service IA est indisponible.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <Head title="Coach IA" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div>
            <h1 class="text-xl font-semibold">Coach IA</h1>
            <p class="text-sm text-muted-foreground">
                Il vous reste {{ remaining }} essai{{ remaining > 1 ? 's' : '' }}
                aujourd'hui.
            </p>
        </div>

        <div
            class="flex min-h-[50vh] flex-1 flex-col gap-4 overflow-y-auto rounded-xl border p-4"
        >
            <p v-if="messages.length === 0" class="text-muted-foreground">
                Posez une question à votre coach Overwatch.
            </p>
            <div
                v-for="(item, index) in messages"
                :key="`${item.role}-${index}`"
                class="max-w-3xl rounded-lg p-3"
                :class="
                    item.role === 'user'
                        ? 'self-end bg-primary text-primary-foreground'
                        : 'self-start bg-muted'
                "
            >
                {{ item.content }}
            </div>
            <p v-if="loading" class="text-sm text-muted-foreground">
                Le coach réfléchit...
            </p>
        </div>

        <p v-if="error" class="text-sm text-destructive">{{ error }}</p>
        <form class="flex gap-2" @submit.prevent="sendMessage">
            <textarea
                v-model="message"
                class="min-h-12 flex-1 resize-none rounded-md border bg-background p-3 text-sm"
                :disabled="loading || remaining === 0"
                placeholder="Votre question..."
                maxlength="2000"
                @keydown.enter.exact.prevent="sendMessage"
            />
            <button
                type="submit"
                class="self-end rounded-md bg-primary px-4 py-3 text-primary-foreground disabled:opacity-50"
                :disabled="loading || !message.trim() || remaining === 0"
            >
                Envoyer
            </button>
        </form>
    </div>
</template>
