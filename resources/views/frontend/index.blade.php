@extends('frontend.layouts.master')
@section('main-content')
    <div class="search-bar">
        <input name="search" placeholder="搜尋商品" type="search">
        <button type="submit"><i class="ti-search"></i></button>
    </div>

    <div class="product-area">
        @if (isset($search))
            <div class="search-panel">
                <span id="search" data-search="{{$search}}">搜尋 '{{$search}}'</span>
                <div class="sort-by">
                    @if ($sortBy === '_score')
                        <span class="cursor active">最相關</span>
                    @else
                        <span class="cursor">最相關</span>
                    @endif
                    <span>|</span>
                    @if ($sortBy === 'price')
                        @if ($sortOrder === 'asc')
                            <span class="cursor active">價格🔺</span>
                        @elseif ($sortOrder === 'desc')
                            <span class="cursor active">價格🔻</span>
                        @endif
                    @else
                        <span class="cursor">價格🔺</span>
                    @endif
                </div>
            </div>
        @endif
        <div class="product-list">
            @foreach($products as $product)
                <div class="single-product">
                    <a href="{{route('product-detail',$product->id)}}">
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
        @if(isset($search))
            <div class="bg-light center">{{ $products->links() }}</div>
        @endif
    </div>
    

    @auth 
        @if(Auth::user()->role=='user')
            <div id="chat-widget">
                <div id="chat-icon" class="chat-icon">
                    <div class="unread unread-total">
                        <div class="unread-count"></div>
                    </div>
                    &#128172;
                </div>
                <div id="chat-box" class="chat-box" data-id="{{Auth::user()->id}}">
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
    <script>
        const productList = $('.product-list');
        let sortBy = null;
        let sortOrder = null;
        let search = null;
        let page = 0;
        $('.search-bar').find('button').click(function(){
            search = $('.search-bar').find('input').val();
            window.location.href = `/product/search?search=${search}`;
        })

        $('.sort-by').find('span').eq(0).click(function(){
            search = $('#search').data('search');
            window.location.href = `/product/search?search=${search}`;
        });

        $('.sort-by').find('span').eq(2).click(function(){
            search = $('#search').data('search');
            sortBy = 'price';
            if ($(this).text() === '價格🔺' && $(this).attr('class') === 'cursor active'){
                sortOrder = 'desc';
            } else {
                sortOrder = 'asc';
            }
            window.location.href = `/product/search?search=${search}&sortBy=${sortBy}&sortOrder=${sortOrder}`;
        });

        </script>
        <script>
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
            let roomId = null;
            let unreadTotalCount = 0;
            let isSubscribe = false;

            chatIcon.addEventListener("click", () => {
                chatBox.style.display = "block";
                chatIcon.style.display = "none";
                messages.scrollTop = messages.scrollHeight;
                @auth 
                    @if(Auth::user()->role=='user')
                        fetchMessages();
                        roomId = chatBox.dataset.id;
                        messageListen('admin');
                    @else
                        if (roomId) {
                            adminFetchMessages(roomId, chatNickname, activeContact);
                            messageListen('user');
                        }
                    @endif
                @endauth
            });

            closeChat.addEventListener("click", () => {
                chatBox.style.display = "none";
                chatIcon.style.display = "flex";
                if (roomId) {
                    leaveListen();
                }
            });
            
            contacts.addEventListener("click", (e) => {
                if (e.target.tagName === "LI") {
                    if (e.target != activeContact){
                        $('.chat-input').hide();
                        $('.chat-content-header').hide();
 
                        if (activeContact) {
                            activeContact.classList.remove("active");
                            Echo.leave('room.' + roomId);
                            isSubscribe = false;
                        }
                        activeContact = e.target;
                        activeContact.classList.add("active");

                        messages.innerHTML = "";
                        roomId = activeContact.dataset.id;
                        chatNickname = activeContact.dataset.name;
                        adminFetchMessages(roomId, chatNickname, activeContact);
                        messageListen('user');
                    }
                }
            });

            async function leaveListen() {
                if (isSubscribe === false) {
                    await messageListen('user');
                }
                Echo.leave('room.' + roomId);
                isSubscribe = false;
            }

            function messageListen(senderRole) {
                return new Promise((resolve) => {
                    Echo.join('room.' + roomId)
                    .here((user) => {
                        isSubscribe = true;
                    })
                    .listen('SendMessage', (e) => {
                        if (e.role === senderRole) {
                            let data = e.message;
                            if (lastMessageDate !== data.date) {
                                addDateLabel(data.date);
                                lastMessageDate = data.date;
                            }
                            addMessage("remote", data.message, data.time);
                            messages.scrollTop = messages.scrollHeight;
                            markMessageAsRead(e.messageId);
                        }
                    });
                });
            }

            async function markMessageAsRead(messageId) {
                try {
                    const response = await fetch('/user/chat/mark-as-read', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        body: JSON.stringify({ 
                            messageId: messageId 
                        })
                    });

                    const data = await response.json();

                } catch (error) {
                    console.error("Error mark messages as read:", error);
                }
            }

            async function fetchChatList() {
                try {
                    const response = await fetch(`/admin/chat/room-list`);
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
                        if (room.unreadCount !== 0) {
                            unreadElement.style.display = 'block';
                            unreadTotalCount += room.unreadCount; 
                        } 

                        chatListElement.appendChild(chatNicknameElement);
                        unreadElement.appendChild(unreadCountElement);
                        chatListElement.appendChild(unreadElement);
                        contacts.appendChild(chatListElement);

                    });
                    if (unreadTotalCount !== 0) {
                        unreadCount.innerHTML = unreadTotalCount;
                        unreadTotal.style.display = 'block'; 
                    }

                } catch (error) {
                    console.error("Error fetching chat list:", error);
                }
            }

            async function adminFetchMessages(roomId, chatNickname, activeContact) {
                try {
                    const response = await fetch(`/admin/chat/messages?id=${roomId}`);
                    const data = await response.json();

                    messages.innerHTML = "";
                    Object.entries(data).forEach(([date, messagesForDate]) => {
                        addDateLabel(date);
                        lastMessageDate = date;

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
                    if (unreadTotalCount !== 0) {
                        unreadCount.innerHTML = unreadTotalCount;
                    } else {
                        unreadTotal.style.display = "none";
                    }
                    unread.style.display = 'none';
                    messages.scrollTop = messages.scrollHeight;
                } catch (error) {
                    console.error("Error fetching messages:", error);
                }
            }

            async function fetchMessages() {
                try {
                    const response = await fetch(`/user/chat/messages`);
                    const data = await response.json();

                    messages.innerHTML = ""; 
                    Object.entries(data).forEach(([date, messagesForDate]) => {
                        addDateLabel(date);
                        lastMessageDate = date;

                        messagesForDate.forEach((message) => {
                            const senderClass = message.sender_id === message.user_id ? "local" : "remote";
                            addMessage(senderClass, message.message, message.time);
                        });
                    });
                    messages.scrollTop = messages.scrollHeight;
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
                } else {
                    messageWrapper.appendChild(messageElement);
                    messageWrapper.appendChild(timeElement);
                }
                messages.appendChild(messageWrapper);
            }

            async function sendMessageToServer(text, roomId) {
                try {
                    const response = await fetch('/user/chat/send', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        },
                        body: JSON.stringify({
                            message: text,
                            roomId: roomId,
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
                    addMessage("local", data.message, data.time);
                    messages.scrollTop = messages.scrollHeight;
                } catch (error) {
                    console.error("Error sending message:", error);
                }
            }

            async function fetchUnreadCount() {
                try {
                    const response = await fetch(`/user/chat/unread`);
                    const data = await response.json();

                    unreadTotalCount = data
                    if (unreadTotalCount !== 0) {
                        unreadCount.innerHTML = unreadTotalCount;
                        unreadTotal.style.display = 'block'; 
                    }
                    
                } catch (error) {
                    console.error("Error fetching unread messages count:", error);
                }
            }

            messageInput.addEventListener("keydown", (e) => {
                if (e.key === "Enter") {
                    sendMessage.click();
                }
            });
            sendMessage.addEventListener("click", () => {
                const text = messageInput.value.trim();
                if (text === "") return;

                sendMessageToServer(text, roomId);
                messageInput.value = ""; 
            });

            @auth 
                @if(Auth::user()->role=='admin')
                    fetchChatList();
                @else
                    fetchUnreadCount();
                @endif
            @endauth
    </script>
@endpush