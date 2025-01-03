@extends('frontend.layouts.master')
@section('main-content')
    <div class="search-bar">
        <form method="POST" name="form" action="">
            @csrf
            <input name="search" placeholder="搜尋商品" type="search">
            <button type="submit"><i class="ti-search"></i></button>
        </form>
    </div>
    <div class="product-list">
        @foreach($products as $product)
            <div class="single-product">
                <a href="{{route('product-detail',$product->slug)}}">
                    <img class="product-img" src="{{asset($product->photo)}}">
                </a>
                <p class="product-title">{{$product->title}}</p>
                <div class="rate-average">
                    <p class="m-0">{{$product->average}}</p>
                    <div class="ratings">
                        <div class="empty-stars"></div>
                        <div class="full-stars" style="width:{{$product->percentage}}%"></div>
                    </div>
                    <p class="text-center">({{$product->reviewCount}})</p>
                </div>
                <p class="product-price">${{$product->price}}</p>
            </div>
        @endforeach
    </div>

    @auth 
        @if(Auth::user()->role=='user')
            <div id="chat-widget">
                <div id="chat-icon" class="chat-icon">&#128172;</div> <!-- 聊天圖示 -->
                <div id="chat-box" class="chat-box">
                    <div id="chat-header">
                        <span>即時對話</span>
                        <button id="close-chat">&times;</button>
                    </div>
                    <div id="chat-main">
                        <div id="hidden" class="chat-list">
                            <ul id="contacts"></ul>
                        </div>
                        <div id="chat-content" class="chat-content">
                            <div id="messages"></div>
                            <div class="chat-input">
                                <input type="text" id="message-input" placeholder="輸入訊息...">
                                <button id="send-message">送出</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else 
            <div id="chat-widget">
                <div id="chat-icon"  class="chat-icon">
                    <div class="unread unread-total">
                        <div class="unread-count"></div>
                    </div>
                    &#128172;
                </div>
                <div id="admin-chat-box" class="chat-box">
                    <div id="chat-header">
                        <span>即時對話</span>
                        <button id="close-chat">&times;</button>
                    </div>
                    <div id="chat-main">
                        <div id="chat-list" class="chat-list">
                            <ul id="contacts"></ul>
                        </div>
                        <div id="chat-content" class="admin-chat-content">
                            <div id="hidden" class="chat-content-header"></div>
                            <div id="messages"></div>
                            <div id="hidden" class="chat-input">
                                <input type="text" id="message-input" placeholder="輸入訊息...">
                                <button id="send-message">送出</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endauth

@endsection
@push('scripts')
    <!--<script src="{{asset('build/assets/app-DG6dFtpO.js')}}"></script>
    <script>
            window.Echo.private('user.2')
            .listen('.UserUpdated', (e) => {
                console.log(e.user);
            });
    </script>-->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const chatIcon = document.querySelector(".chat-icon");
            const chatBox = document.querySelector(".chat-box");
            const closeChat = document.getElementById("close-chat");
            const sendMessage = document.getElementById("send-message");
            const messageInput = document.getElementById("message-input");
            const contacts = document.getElementById("contacts");
            const contentHeader = document.querySelector(".chat-content-header");
            const unreadTotal = document.querySelector(".unread-total");
            const messages = document.getElementById("messages");
            const unreadCount = document.querySelector(".unread-count");

            let activeContact = null;
            let lastMessageDate = null;
            let chatId = null;
            let unreadTotalCount = 0;

            chatIcon.addEventListener("click", () => {
                chatBox.style.display = "flex";
                chatIcon.style.display = "none";
            });

            closeChat.addEventListener("click", () => {
                chatBox.style.display = "none";
                chatIcon.style.display = "flex";
            });
            
            contacts.addEventListener("click", (e) => {
                if (e.target.tagName === "LI") {
                    if (e.target != activeContact){
                        $('.chat-input').hide();
                        $('.chat-content-header').hide();
                        if (activeContact) {
                            activeContact.classList.remove("active");
                        }
                        activeContact = e.target;
                        activeContact.classList.add("active");

                        messages.innerHTML = "";
                        chatId = activeContact.dataset.id;
                        chatNickname = activeContact.dataset.name;
                        adminFetchMessages(chatId, chatNickname, activeContact);
                    }
                }
            });

            async function fetchChatList() {
                try {
                    const response = await fetch(`/user/chat/chat-list`);
                    const data = await response.json();
                    contacts.innerHTML = '';
                    unreadTotalCount = 0;
                    data.forEach((room) => {
                        const chatListElement = document.createElement("li");
                        chatListElement.dataset.id = room.id;
                        chatListElement.dataset.name = room.nickname;

                        const chatNicknameElement = document.createElement("div");
                        chatNicknameElement.className = 'room-name';
                        chatNicknameElement.textContent = room.nickname;

                        const unreadElement = document.createElement("div");
                        unreadElement.className = 'unread';

                        const unreadCountElement = document.createElement("div");
                        unreadCountElement.className = 'center unread-count';
                        unreadCountElement.textContent = room.unreadCount;
                        if (room.unreadCount == 0) {
                            unreadElement.style.display = 'none';
                        }else {
                            unreadTotalCount += room.unreadCount; 
                        }

                        chatListElement.appendChild(chatNicknameElement);
                        unreadElement.appendChild(unreadCountElement);
                        chatListElement.appendChild(unreadElement);
                        contacts.appendChild(chatListElement);

                    });
                    unreadCount.innerHTML = unreadTotalCount;

                } catch (error) {
                    console.error("Error fetching messages:", error);
                }
            }

            async function adminFetchMessages(chatId, chatNickname, activeContact) {
                try {
                    const response = await fetch(`/admin/chat/messages?id=${chatId}`);
                    const data = await response.json();

                    messages.innerHTML = ""; // 清空訊息

                    // 遍歷分組的日期和訊息
                    Object.entries(data).forEach(([date, messagesForDate]) => {
                        // 插入日期標籤
                        addDateLabel(date);
                        lastMessageDate = date;

                        // 插入當天的訊息
                        messagesForDate.forEach((message) => {
                            const senderClass = message.sender_id === message.user_id ? "local" : "remote";
                            addMessage(senderClass, message.message, message.time);
                        });
                    });
                    $('.chat-input').show();
                    $('.chat-content-header').show();
                    contentHeader.textContent = chatNickname;
                    const unread = activeContact.querySelector(".unread");
                    const unreadCountElement = activeContact.querySelector(".unread-count");
                    unreadTotalCount -= unreadCountElement.textContent;
                    if (unreadTotalCount == 0) {
                        unreadTotal.style.display = "none";
                    }else {
                        unreadCount.innerHTML = unreadTotalCount;
                    }
                    unread.style.display = 'none';
                } catch (error) {
                    console.error("Error fetching messages:", error);
                }
            }

            async function fetchMessages() {
                try {
                    const response = await fetch(`/user/chat/messages`);
                    const data = await response.json();

                    messages.innerHTML = ""; // 清空訊息

                    // 遍歷分組的日期和訊息
                    Object.entries(data).forEach(([date, messagesForDate]) => {
                        // 插入日期標籤
                        addDateLabel(date);
                        lastMessageDate = date;

                        // 插入當天的訊息
                        messagesForDate.forEach((message) => {
                            const senderClass = message.sender_id === message.user_id ? "local" : "remote";
                            addMessage(senderClass, message.message, message.time);
                        });
                    });

                } catch (error) {
                    console.error("Error fetching messages:", error);
                }
            }

            function addDateLabel(date) {
                const dateLabel = document.createElement("div");
                dateLabel.className = "date-label";
                dateLabel.textContent = date;
                messages.appendChild(dateLabel);
            }

            function addMessage(sender, text, time) {
                const messageWrapper = document.createElement("div");
                messageWrapper.className = `message-wrapper ${sender}`;

                const messageElement = document.createElement("div");
                messageElement.className = `message ${sender}`;
                messageElement.textContent = text;

                const timeElement = document.createElement("span");
                timeElement.className = "message-time";
                timeElement.textContent = time;

                if(sender=='local') {
                    messageWrapper.appendChild(timeElement);
                    messageWrapper.appendChild(messageElement);
                }else{
                    messageWrapper.appendChild(messageElement);
                    messageWrapper.appendChild(timeElement);
                }
                messages.appendChild(messageWrapper);
                messages.scrollTop = messages.scrollHeight; // 自動滾動到最新訊息
            }

            // 發送訊息
            async function sendMessageToServer(text, chatId) {
                try {
                    const response = await fetch('/user/chat/send', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        },
                        body: JSON.stringify({
                            message: text,
                            chatId: chatId,
                        }),
                    });

                    if (!response.ok) {
                        const errorText = await response.text();
                        throw new Error(`Server error: ${errorText}`);
                    }
                    
                    const data = await response.json();

                    if (lastMessageDate !== data.date) {
                        addDateLabel(data.date);
                        lastMessageDate = data.date;
                    }
                    // 新增訊息到畫面
                    addMessage("local", data.message, data.time);
                } catch (error) {
                    console.error("Error sending message:", error);
                }
            }

            // 監聽按下送出鍵
            sendMessage.addEventListener("click", () => {
                const text = messageInput.value.trim();
                if (text === "") return; // 防止空訊息

                // 送出訊息到後端並更新畫面
                sendMessageToServer(text, chatId);
                messageInput.value = ""; // 清空輸入框
            });

            fetchMessages();
            fetchChatList();

            /*async function fetchChatList() {
                try {
                    const response = await fetch(`/ccc`);
                    const data = await response.json();

                    console.log(data);
                } catch (error) {
                    console.error("Error fetching messages:", error);
                }
            }

            fetchChatList();*/
        });

    </script>
@endpush