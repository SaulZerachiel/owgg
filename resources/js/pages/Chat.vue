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
    return document
        .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
        ?.getAttribute('content') ?? undefined;
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
            throw new Error(data.message ?? 'Impossible de charger cette conversation.');
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
            ...conversations.value.filter((item) => item.id !== conversation.id),
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
                :class="{ 'is-active': activeConversationId === conversation.id }"
                @click="selectConversation(conversation.id)"
            >
                <span class="conversation-item-mark">◈</span>
                <span class="conversation-item-title">{{ conversation.title }}</span>
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
                        Il vous reste {{ remaining }} essai{{ remaining > 1 ? 's' : '' }}
                        aujourd'hui.
                    </p>
                </div>
                <span class="chat-status"><i></i> ONLINE</span>
            </header>

            <div ref="messagesElement" class="chat-messages">
                <p v-if="messages.length === 0" class="chat-empty">
                    Posez une question à Coach Winston pour commencer une analyse.
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
                    <div v-if="item.role === 'assistant'" class="message-content" v-html="renderMarkdown(item.content)" />
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
                    :disabled="loading || loadingConversation || remaining === 0"
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
    background: #091019;
    color: #eef6f8;
}

.conversation-sidebar {
    min-height: 0;
    overflow: hidden;
    border-right: 1px solid #a9e8ff1f;
    background: linear-gradient(180deg, #0d1b27, #091019);
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
    color: #f06432;
    font-size: 0.62rem;
    font-weight: 800;
    letter-spacing: 0.16em;
}

.conversation-sidebar h2,
.chat-header h1 {
    margin: 0;
    font-family: 'Arial Narrow', 'Trebuchet MS', sans-serif;
    letter-spacing: -0.03em;
}

.conversation-sidebar h2 {
    font-size: 1.2rem;
}

.new-conversation-button {
    display: grid;
    width: 30px;
    height: 30px;
    place-items: center;
    border: 1px solid #f0643266;
    color: #f06432;
    font-size: 1.35rem;
    line-height: 1;
}

.conversation-item {
    display: flex;
    width: 100%;
    align-items: center;
    gap: 9px;
    border: 1px solid transparent;
    padding: 10px 9px;
    color: #a7bec7;
    text-align: left;
    transition: border-color 0.2s, background 0.2s, color 0.2s;
}

.conversation-item:hover,
.conversation-item.is-active {
    border-color: #a9e8ff2b;
    background: #a9e8ff0d;
    color: #eef6f8;
}

.conversation-item.is-active {
    border-left: 2px solid #f06432;
}

.conversation-item-mark {
    color: #f06432;
    font-size: 0.8rem;
}

.conversation-item-title {
    overflow: hidden;
    font-size: 0.82rem;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.conversation-empty {
    color: #78909a;
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
}

.chat-header p:last-child {
    margin: 5px 0 0;
    color: #8ca5af;
    font-size: 0.82rem;
}

.chat-status {
    color: #7fd8ae;
    font-size: 0.68rem;
    font-weight: 800;
    letter-spacing: 0.14em;
}

.chat-status i {
    display: inline-block;
    width: 7px;
    height: 7px;
    margin-right: 6px;
    border-radius: 50%;
    background: #7fd8ae;
    box-shadow: 0 0 10px #7fd8ae;
}

.chat-messages {
    display: flex;
    min-height: 0;
    flex: 1 1 auto;
    flex-direction: column;
    gap: 14px;
    overflow-y: auto;
    border: 1px solid #a9e8ff1f;
    background: #050a0e99;
    padding: clamp(14px, 2vw, 24px);
}

.chat-empty,
.chat-loading {
    color: #8ca5af;
    font-size: 0.9rem;
}

.message-bubble {
    max-width: min(820px, 86%);
    padding: 12px 15px;
    line-height: 1.55;
}

.message-user {
    align-self: flex-end;
    border: 1px solid #f0643266;
    background: #f064321c;
}

.message-assistant {
    align-self: flex-start;
    border: 1px solid #a9e8ff1f;
    background: #a9e8ff0b;
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
    color: #a9e8ff;
    font-size: 1rem;
}

.message-content :deep(ul),
.message-content :deep(ol) {
    margin: 0.45rem 0 0.7rem 1.25rem;
}

.message-content :deep(strong) {
    color: #f7c7a9;
}

.message-content :deep(code) {
    border-radius: 3px;
    background: #00000066;
    padding: 0.1rem 0.3rem;
    color: #f7c7a9;
}

.chat-error {
    flex: 0 0 auto;
    margin: 10px 0 0;
    color: #ff9a8b;
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
    border: 1px solid #a9e8ff2b;
    background: #050a0e;
    padding: 13px;
    color: #eef6f8;
    outline: none;
}

.chat-composer textarea:focus {
    border-color: #f06432;
}

.chat-composer button {
    align-self: flex-end;
    border: 1px solid #f06432;
    background: #f06432;
    padding: 13px 18px;
    color: #091019;
    font-weight: 800;
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
        border-bottom: 1px solid #a9e8ff1f;
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
</style>
