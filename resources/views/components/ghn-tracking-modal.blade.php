@props(['order'])

@php
    $ghn = $order->ghn_tracking;
@endphp

<div x-show="showGhnModal"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 text-left"
     style="background-color: rgba(0, 0, 0, 0.65); backdrop-filter: blur(4px); display: none;"
     @click.self="showGhnModal = false"
     @keydown.escape.window="showGhnModal = false">

    <div class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl overflow-hidden flex flex-col max-h-[90vh]"
         style="border: 1px solid #EADFCF;"
         @click.stop>
        
        {{-- Modal Header: GHN Express Orange Gradient --}}
        <div style="background: linear-gradient(135deg, #F26522 0%, #EA5410 50%, #C94205 100%) !important; color: #ffffff !important; padding: 22px 24px; position: relative;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px;">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="width: 48px; height: 48px; border-radius: 16px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center; font-size: 22px; color: #ffffff; flex-shrink: 0; border: 1px solid rgba(255, 255, 255, 0.3); box-shadow: inset 0 0 8px rgba(255, 255, 255, 0.2);">
                        <i class="fa-solid fa-truck-fast"></i>
                    </div>
                    <div>
                        <div style="font-size: 11px; font-weight: 800; letter-spacing: 1px; color: #FFE082; text-transform: uppercase; display: flex; align-items: center; gap: 6px;">
                            <span>Giao Hàng Nhanh (GHN Express)</span>
                            <span style="width: 7px; height: 7px; border-radius: 50%; background: #4ADE80; display: inline-block;"></span>
                        </div>
                        <h3 style="font-weight: 900; font-size: 18px; color: #ffffff !important; margin: 4px 0 0; letter-spacing: -0.3px;">
                            Thông tin vận chuyển
                        </h3>
                    </div>
                </div>

                <button type="button" @click="showGhnModal = false" 
                        style="width: 36px; height: 36px; border-radius: 12px; background: rgba(255, 255, 255, 0.22); color: #ffffff; border: none; display: flex; align-items: center; justify-content: center; font-size: 16px; cursor: pointer; transition: all 0.2s;"
                        onmouseover="this.style.background='rgba(255,255,255,0.35)'"
                        onmouseout="this.style.background='rgba(255,255,255,0.22)'">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            {{-- Tracking Code & Quick Actions Bar --}}
            <div style="margin-top: 16px; padding-top: 14px; border-top: 1px solid rgba(255, 255, 255, 0.25); display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; font-size: 12px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="color: rgba(255, 255, 255, 0.9);">Mã vận đơn:</span>
                    <span style="font-family: monospace; font-weight: 900; font-size: 15px; color: #FFE082; letter-spacing: 0.5px;">{{ $ghn['tracking_code'] }}</span>
                    <button type="button" 
                            @click="navigator.clipboard.writeText('{{ $ghn['tracking_code'] }}').then(() => { copiedGhn = true; setTimeout(() => copiedGhn = false, 2000); })"
                            style="padding: 3px 10px; background: rgba(255, 255, 255, 0.25); border: 1px solid rgba(255, 255, 255, 0.35); color: #ffffff; font-size: 11px; font-weight: 700; border-radius: 8px; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 4px;">
                        <template x-if="!copiedGhn">
                            <span style="display: inline-flex; align-items: center; gap: 4px;">
                                <i class="fa-regular fa-copy"></i>
                                <span>Sao chép</span>
                            </span>
                        </template>
                        <template x-if="copiedGhn">
                            <span style="display: inline-flex; align-items: center; gap: 4px; color: #86EFAC;">
                                <i class="fa-solid fa-check"></i>
                                <span>Đã chép!</span>
                            </span>
                        </template>
                    </button>
                </div>

                <div style="font-size: 11.5px; color: #ffffff; background: rgba(0, 0, 0, 0.15); padding: 4px 10px; border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.2);">
                    <span>Tổng đài GHN: <strong style="color: #FFE082;">1900 636677</strong></span>
                </div>
            </div>
        </div>

        {{-- Modal Body: Scrollable Content --}}
        <div style="padding: 20px; overflow-y: auto; background-color: #FAF6EE; display: flex; flex-direction: column; gap: 16px;">

            {{-- Summary Status Card --}}
            <div style="background: #ffffff; border-radius: 18px; padding: 18px; border: 1px solid #EADFCF; box-shadow: 0 2px 8px rgba(78, 52, 46, 0.04);">
                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
                    <div>
                        <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #795548; letter-spacing: 0.5px;">Trạng thái bưu kiện hiện tại</div>
                        <div style="font-size: 14.5px; font-weight: 800; color: #4E342E; margin-top: 4px; line-height: 1.4;">
                            {{ $ghn['current_location'] }}
                        </div>
                    </div>
                    <span style="padding: 4px 12px; border-radius: 9999px; font-size: 11.5px; font-weight: 800; flex-shrink: 0;
                        @if($order->order_status === 'COMPLETED')
                            background: #E8F5E9; color: #2E7D32; border: 1px solid #A5D6A7;
                        @elseif($order->order_status === 'SHIPPING')
                            background: #FFF3E0; color: #E65100; border: 1px solid #FFCC80;
                        @else
                            background: #FFF8E7; color: #B87309; border: 1px solid #F6D89B;
                        @endif">
                        {{ $order->order_status === 'COMPLETED' ? 'Đã giao thành công' : ($order->order_status === 'SHIPPING' ? 'Đang giao hàng' : 'Đang xử lý') }}
                    </span>
                </div>

                {{-- Delivery Destination Overview --}}
                <div style="margin-top: 14px; padding-top: 12px; border-top: 1px solid #F2EAE0; display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 10px; font-size: 12px;">
                    <div>
                        <span style="color: #795548;">Người nhận:</span>
                        <strong style="color: #4E342E; margin-left: 4px;">{{ $order->recipient_name }}</strong> ({{ $order->recipient_phone }})
                    </div>
                    <div>
                        <span style="color: #795548;">Địa chỉ phát:</span>
                        <strong style="color: #4E342E; margin-left: 4px;">{{ $order->recipient_address }}</strong>
                    </div>
                </div>
            </div>

            {{-- Shipper Information (If shipping or completed) --}}
            @if(in_array($order->order_status, ['SHIPPING', 'COMPLETED']))
                <div style="background: #ffffff; border-radius: 18px; padding: 16px 18px; border: 1px solid #EADFCF; display: flex; align-items: center; justify-content: space-between; gap: 12px; box-shadow: 0 2px 8px rgba(78, 52, 46, 0.04);">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div style="width: 44px; height: 44px; border-radius: 14px; background: #FFF3E0; color: #F26522; border: 1px solid #FFE0B2; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                            <i class="fa-solid fa-motorcycle"></i>
                        </div>
                        <div>
                            <div style="font-size: 11px; font-weight: 800; color: #795548; text-transform: uppercase;">Bưu tá phụ trách giao hàng</div>
                            <div style="font-weight: 800; font-size: 14px; color: #4E342E; margin-top: 2px;">
                                {{ $ghn['shipper']['name'] }} <span style="font-weight: 500; font-size: 12px; color: #795548;">(Biển số: {{ $ghn['shipper']['license_plate'] }})</span>
                            </div>
                        </div>
                    </div>
                    <a href="tel:{{ $ghn['shipper']['phone'] }}" 
                       style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; background: #10B981; color: #ffffff; font-weight: 800; font-size: 12px; border-radius: 12px; text-decoration: none; transition: background 0.2s; flex-shrink: 0;"
                       onmouseover="this.style.background='#059669'"
                       onmouseout="this.style.background='#10B981'">
                        <i class="fa-solid fa-phone"></i>
                        <span>Gọi bưu tá</span>
                    </a>
                </div>
            @endif

            {{-- Timeline Checklist of Warehouses --}}
            <div style="background: #ffffff; border-radius: 18px; padding: 20px; border: 1px solid #EADFCF; box-shadow: 0 2px 8px rgba(78, 52, 46, 0.04);">
                <div style="font-size: 12px; font-weight: 900; text-transform: uppercase; color: #4E342E; letter-spacing: 0.5px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-route" style="color: #F26522;"></i>
                    <span>Chi tiết đơn hàng di chuyển</span>
                </div>

                <div style="position: relative; padding-left: 28px; display: flex; flex-direction: column; gap: 24px;">
                    {{-- Connecting Line --}}
                    <div style="position: absolute; left: 10px; top: 10px; bottom: 10px; width: 2px; background: #EADFCF;"></div>

                    @foreach($ghn['events'] as $event)
                        <div style="position: relative;">
                            {{-- Step Indicator Bullet --}}
                            <div style="position: absolute; left: -28px; top: 2px; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 800;
                                @if($event['status'] === 'current')
                                    background: #F26522; color: #ffffff; box-shadow: 0 0 0 4px rgba(242, 101, 34, 0.25);
                                @else
                                    background: #10B981; color: #ffffff;
                                @endif">
                                @if($event['status'] === 'current')
                                    <i class="fa-solid fa-arrow-down"></i>
                                @else
                                    <i class="fa-solid fa-check"></i>
                                @endif
                            </div>

                            {{-- Event Details --}}
                            <div>
                                <div style="display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: 8px;">
                                    <h5 style="font-size: 13.5px; font-weight: 800; margin: 0; {{ $event['status'] === 'current' ? 'color: #F26522;' : 'color: #4E342E;' }}">
                                        {{ $event['title'] }}
                                    </h5>
                                    <span style="font-size: 11px; font-weight: 700; color: #8E8076; font-family: monospace;">
                                        {{ $event['time'] }}
                                    </span>
                                </div>

                                {{-- Warehouse / Hub Location Badge --}}
                                <div style="margin-top: 6px; display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px; border-radius: 8px; font-size: 11px; font-weight: 700; background: #FAF6EE; color: #5C3219; border: 1px solid #EADFCF;">
                                    <i class="fa-solid fa-location-dot" style="color: #E08A1E; font-size: 10px;"></i>
                                    <span>{{ $event['location'] }}</span>
                                </div>

                                <p style="font-size: 12px; color: #795548; margin: 6px 0 0; line-height: 1.6;">
                                    {{ $event['description'] }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Policy Notice --}}
            <div style="padding: 12px 16px; background: rgba(255, 248, 231, 0.8); border-radius: 14px; border: 1px solid #F6D89B; font-size: 11.5px; color: #795548; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-shield-halved" style="color: #D97706; font-size: 16px; flex-shrink: 0;"></i>
                <span>Kiện hàng được bảo hiểm 100% giá trị trong suốt quá trình vận chuyển. Đồng kiểm khi nhận hàng.</span>
            </div>

        </div>

        {{-- Modal Footer --}}
        <div style="padding: 14px 20px; background: #ffffff; border-top: 1px solid #EADFCF; display: flex; justify-content: flex-end;">
            <button type="button" @click="showGhnModal = false"
                    style="padding: 8px 20px; background: #FAF6EE; color: #5C3219; font-weight: 800; font-size: 12.5px; border-radius: 12px; border: 1px solid #EADFCF; cursor: pointer; transition: all 0.2s;"
                    onmouseover="this.style.background='#F2EAE0'"
                    onmouseout="this.style.background='#FAF6EE'">
                Đóng cửa sổ
            </button>
        </div>

    </div>
</div>
