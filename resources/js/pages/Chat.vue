<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import DOMPurify from 'dompurify';
import { marked } from 'marked';
import { nextTick, ref } from 'vue';

type Message = {
    role: 'user' | 'assistant';
    content: string;
};

type Conversation = {
    id: string;
    title: string;
    updated_at?: string;
};

const props = defineProps<{
    remaining: number;
    conversations: Conversation[];
}>();

const conversations = ref<Conversation[]>(props.conversations);
const activeConversationId = ref<string | null>(null);
const messages = ref<Message[]>([]);
const message = ref('');
const remaining = ref(props.remaining);
const loading = ref(false);
const loadingConversation = ref(false);
const error = ref('');
const messagesElement = ref<HTMLElement | null>(null);

function renderMarkdown(content: string): string {
    return DOMPurify.sanitize(marked.parse(content, { async: false }));
}

async function scrollConversationToBottom() {
    await nextTick();
    if (messagesElement.value) {
        messagesElement.value.scrollTop = messagesElement.value.scrollHeight;
    }
}

function csrfToken(): string | undefined {
    return (
        document
            .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? undefined
    );
}

async function selectConversation(conversationId: string) {
    if (loadingConversation.value || loading.value) {
        return;
    }

    error.value = '';
    loadingConversation.value = true;

    try {
        const response = await fetch(`/chat/conversations/${conversationId}`, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });
        const data = await response.json();

        if (!response.ok) {
            throw new Error(
                data.message ?? 'Impossible de charger cette conversation.',
            );
        }

        activeConversationId.value = data.conversation.id;
        messages.value = data.messages;
        await scrollConversationToBottom();
    } catch (exception) {
        error.value =
            exception instanceof Error
                ? exception.message
                : 'Impossible de charger cette conversation.';
    } finally {
        loadingConversation.value = false;
    }
}

function startNewConversation() {
    if (loading.value) {
        return;
    }

    activeConversationId.value = null;
    messages.value = [];
    message.value = '';
    error.value = '';
}

function updateConversation(conversation: Conversation) {
    const existing = conversations.value.findIndex(
        (item) => item.id === conversation.id,
    );

    if (existing === -1) {
        conversations.value.unshift(conversation);
    } else {
        conversations.value[existing] = conversation;
        conversations.value = [
            conversation,
            ...conversations.value.filter(
                (item) => item.id !== conversation.id,
            ),
        ];
    }
}

async function sendMessage() {
    const content = message.value.trim();

    if (!content || loading.value || remaining.value === 0) {
        return;
    }

    error.value = '';
    loading.value = true;
    message.value = '';
    messages.value.push({ role: 'user', content });
    await scrollConversationToBottom();

    try {
        const response = await fetch('/chat/message', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                ...(csrfToken() ? { 'X-CSRF-TOKEN': csrfToken() } : {}),
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                message: content,
                conversation_id: activeConversationId.value,
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
        activeConversationId.value = data.conversation.id;
        remaining.value = data.remaining;
        updateConversation(data.conversation);
        await scrollConversationToBottom();
    } catch (exception) {
        messages.value.pop();
        error.value =
            exception instanceof Error
                ? exception.message
                : 'Le service de Coach Winston est indisponible.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <Head title="Coach Winston" />

    <div class="chat-shell">
        <aside class="conversation-sidebar">
            <div class="conversation-sidebar-header">
                <div>
                    <p class="conversation-kicker">ARCHIVES // WINSTON</p>
                    <h2>Conversations</h2>
                </div>
                <button
                    type="button"
                    class="new-conversation-button"
                    title="Nouvelle conversation"
                    @click="startNewConversation"
                >
                    +
                </button>
            </div>

            <button
                v-for="conversation in conversations"
                :key="conversation.id"
                type="button"
                class="conversation-item"
                :class="{
                    'is-active': activeConversationId === conversation.id,
                }"
                @click="selectConversation(conversation.id)"
            >
                <span class="conversation-item-mark">◈</span>
                <span class="conversation-item-title">{{
                    conversation.title
                }}</span>
            </button>

            <p v-if="conversations.length === 0" class="conversation-empty">
                Vos prochaines analyses apparaîtront ici.
            </p>
        </aside>

        <section class="chat-main">
            <header class="chat-header">
                <div>
                    <p class="chat-kicker">TACTICAL CORE // WINSTON</p>
                    <h1>Coach Winston</h1>
                    <p>
                        Il vous reste {{ remaining }} essai{{
                            remaining > 1 ? 's' : ''
                        }}
                        aujourd'hui.
                    </p>
                </div>
                <span class="chat-status"><i></i> ONLINE</span>
            </header>

            <div ref="messagesElement" class="chat-messages">
                <p v-if="messages.length === 0" class="chat-empty">
                    Posez une question à Coach Winston pour commencer une
                    analyse.
                </p>
                <div
                    v-for="(item, index) in messages"
                    :key="`${item.role}-${index}`"
                    class="message-bubble"
                    :class="
                        item.role === 'user'
                            ? 'message-user'
                            : 'message-assistant'
                    "
                >
                    <div
                        v-if="item.role === 'assistant'"
                        class="message-content"
                        v-html="renderMarkdown(item.content)"
                    />
                    <div v-else class="message-content">{{ item.content }}</div>
                </div>
                <p v-if="loading" class="chat-loading">
                    Coach Winston réfléchit...
                </p>
            </div>

            <p v-if="error" class="chat-error">{{ error }}</p>
            <form class="chat-composer" @submit.prevent="sendMessage">
                <textarea
                    v-model="message"
                    :disabled="
                        loading || loadingConversation || remaining === 0
                    "
                    placeholder="Votre question..."
                    maxlength="2000"
                    @keydown.enter.exact.prevent="sendMessage"
                />
                <button
                    type="submit"
                    :disabled="loading || !message.trim() || remaining === 0"
                >
                    Envoyer
                </button>
            </form>
        </section>
    </div>
</template>

<style scoped>
.chat-shell {
    display: grid;
    height: calc(100dvh - 76px);
    min-height: 0;
    grid-template-columns: minmax(210px, 260px) minmax(0, 1fr);
    overflow: hidden;
    background: var(--bg-0);
    color: var(--text);
}

.conversation-sidebar {
    min-height: 0;
    overflow: hidden;
    border-right: 1px solid var(--surface-line);
    background: var(--bg-1);
    padding: 22px 14px;
}

.conversation-sidebar-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 20px;
}

.conversation-kicker,
.chat-kicker {
    margin: 0 0 7px;
    color: var(--ow-orange);
    font-size: 0.62rem;
    font-weight: 800;
    letter-spacing: 0.16em;
}

.conversation-sidebar h2,
.chat-header h1 {
    margin: 0;
    font-family: var(--font-heading);
    letter-spacing: -0.03em;
    font-style: italic;
}

.conversation-sidebar h2 {
    font-size: 1.2rem;
}

.new-conversation-button {
    display: grid;
    width: 30px;
    height: 30px;
    place-items: center;
    border: 1px solid color-mix(in srgb, var(--ow-orange) 60%, transparent);
    background: var(--bg-2);
    color: var(--ow-orange);
    font-size: 1.35rem;
    line-height: 1;
    clip-path: polygon(0 0, calc(100% - 7px) 0, 100% 7px, 100% 100%, 0 100%);
    transition:
        background-color 150ms cubic-bezier(0.2, 0.8, 0.2, 1),
        color 150ms cubic-bezier(0.2, 0.8, 0.2, 1),
        transform 150ms cubic-bezier(0.2, 0.8, 0.2, 1);
}

.new-conversation-button:hover {
    background: var(--ow-orange);
    color: var(--bg-0);
    transform: translateY(-1px);
}

.new-conversation-button:focus-visible,
.conversation-item:focus-visible,
.chat-composer button:focus-visible {
    outline: 2px solid var(--ow-orange);
    outline-offset: 3px;
}

.conversation-item {
    display: flex;
    width: 100%;
    align-items: center;
    gap: 9px;
    border: 1px solid transparent;
    padding: 10px 9px;
    background: transparent;
    color: var(--text-dim);
    text-align: left;
    clip-path: polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 0 100%);
    transition:
        border-color 150ms cubic-bezier(0.2, 0.8, 0.2, 1),
        background-color 150ms cubic-bezier(0.2, 0.8, 0.2, 1),
        color 150ms cubic-bezier(0.2, 0.8, 0.2, 1);
}

.conversation-item:hover,
.conversation-item.is-active {
    border-color: var(--surface-line-strong);
    background: var(--bg-3);
    color: var(--text);
}

.conversation-item.is-active {
    border-left: 2px solid var(--ow-orange);
}

.conversation-item-mark {
    color: var(--ow-orange);
    font-size: 0.8rem;
}

.conversation-item-title {
    overflow: hidden;
    font-size: 0.82rem;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.conversation-empty {
    color: var(--text-mute);
    font-size: 0.8rem;
    line-height: 1.5;
}

.chat-main {
    display: flex;
    min-width: 0;
    min-height: 0;
    flex-direction: column;
    overflow: hidden;
    padding: clamp(18px, 3vh, 30px) clamp(18px, 4vw, 52px);
}

.chat-header {
    display: flex;
    flex: 0 0 auto;
    align-items: flex-end;
    justify-content: space-between;
    gap: 20px;
    padding-bottom: 18px;
}

.chat-header h1 {
    font-size: clamp(1.7rem, 3vw, 2.5rem);
    text-transform: uppercase;
}

.chat-header p:last-child {
    margin: 5px 0 0;
    color: var(--text-dim);
    font-size: 0.82rem;
}

.chat-status {
    color: var(--win);
    font-size: 0.68rem;
    font-weight: 800;
    letter-spacing: 0.14em;
}

.chat-status i {
    display: inline-block;
    width: 7px;
    height: 7px;
    margin-right: 6px;
    background: var(--win);
}

.chat-messages {
    display: flex;
    min-height: 0;
    flex: 1 1 auto;
    flex-direction: column;
    gap: 14px;
    overflow-y: auto;
    border: 1px solid var(--surface-line);
    background: var(--bg-1);
    padding: clamp(14px, 2vw, 24px);
    clip-path: polygon(0 0, calc(100% - 12px) 0, 100% 12px, 100% 100%, 0 100%);
}

.chat-empty,
.chat-loading {
    color: var(--text-dim);
    font-size: 0.9rem;
}

.message-bubble {
    max-width: min(820px, 86%);
    padding: 12px 15px;
    line-height: 1.55;
}

.message-user {
    align-self: flex-end;
    border: 1px solid color-mix(in srgb, var(--ow-orange) 42%, transparent);
    background: color-mix(in srgb, var(--ow-orange) 10%, var(--bg-1));
    clip-path: polygon(0 0, calc(100% - 10px) 0, 100% 10px, 100% 100%, 0 100%);
}

.message-assistant {
    align-self: flex-start;
    border: 1px solid var(--surface-line);
    background: var(--bg-2);
    clip-path: polygon(0 0, calc(100% - 10px) 0, 100% 10px, 100% 100%, 0 100%);
}

.message-content {
    overflow-wrap: anywhere;
    font-size: 0.92rem;
}

.message-content :deep(p) {
    margin: 0 0 0.7rem;
}

.message-content :deep(p:last-child) {
    margin-bottom: 0;
}

.message-content :deep(h1),
.message-content :deep(h2),
.message-content :deep(h3) {
    margin: 0.8rem 0 0.35rem;
    color: var(--role-tank);
    font-size: 1rem;
}

.message-content :deep(ul),
.message-content :deep(ol) {
    margin: 0.45rem 0 0.7rem 1.25rem;
}

.message-content :deep(strong) {
    color: var(--ow-orange-bright);
}

.message-content :deep(code) {
    background: var(--bg-0);
    padding: 0.1rem 0.3rem;
    color: var(--ow-orange-bright);
}

.chat-error {
    flex: 0 0 auto;
    margin: 10px 0 0;
    color: var(--loss);
    font-size: 0.82rem;
}

.chat-composer {
    display: flex;
    flex: 0 0 auto;
    gap: 10px;
    padding-top: 14px;
}

.chat-composer textarea {
    min-height: 48px;
    max-height: 130px;
    flex: 1;
    resize: vertical;
    border: 1px solid var(--surface-line-strong);
    background: var(--bg-1);
    padding: 13px;
    color: var(--text);
    outline: none;
    clip-path: polygon(0 0, calc(100% - 10px) 0, 100% 10px, 100% 100%, 0 100%);
}

.chat-composer textarea:focus {
    border-color: var(--ow-orange);
    box-shadow: 0 2px 0 var(--ow-orange);
}

.chat-composer button {
    align-self: flex-end;
    border: 1px solid var(--ow-orange);
    background: var(--ow-orange);
    padding: 13px 18px;
    color: var(--bg-0);
    clip-path: polygon(
        0 0,
        calc(100% - 10px) 0,
        100% 10px,
        100% 100%,
        10px 100%,
        0 calc(100% - 10px)
    );
    cursor: pointer;
    font-family: var(--font-heading);
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    transition:
        filter 150ms cubic-bezier(0.2, 0.8, 0.2, 1),
        transform 150ms cubic-bezier(0.2, 0.8, 0.2, 1),
        box-shadow 150ms cubic-bezier(0.2, 0.8, 0.2, 1);
}

.chat-composer button:hover:not(:disabled) {
    box-shadow: 0 0 20px var(--ow-orange-glow);
    filter: brightness(1.05);
    transform: translateY(-1px);
}

.chat-composer button:disabled {
    cursor: not-allowed;
    opacity: 0.45;
}

@media (max-width: 720px) {
    .chat-shell {
        grid-template-columns: 1fr;
        grid-template-rows: auto minmax(0, 1fr);
    }

    .conversation-sidebar {
        display: flex;
        max-height: 145px;
        gap: 8px;
        overflow: hidden;
        border-right: 0;
        border-bottom: 1px solid var(--surface-line);
        padding: 10px;
    }

    .conversation-sidebar-header {
        min-width: 160px;
        margin: 0;
    }

    .conversation-item {
        width: 170px;
        flex: 0 0 auto;
    }

    .chat-main {
        padding: 16px 12px;
    }

    .chat-header {
        align-items: flex-start;
    }

    .chat-status {
        display: none;
    }
}

@media (prefers-reduced-motion: reduce) {
    .new-conversation-button,
    .conversation-item,
    .chat-composer button {
        transition: none;
    }

    .new-conversation-button:hover,
    .chat-composer button:hover {
        transform: none;
    }
}
</style>
